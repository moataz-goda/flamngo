<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardAnalyticsService
{
    public function __construct(protected ProductService $products)
    {
    }

    /**
     * @return array{
     *     period: int,
     *     from: string,
     *     to: string,
     *     salesTrend: array{labels: list<string>, values: list<float>},
     *     topProducts: array{labels: list<string>, quantities: list<int>, revenues: list<float>},
     *     topCustomers: array{labels: list<string>, revenues: list<float>, orders: list<int>},
     *     topItems: array{labels: list<string>, quantities: list<int>, revenues: list<float>},
     *     restock: array{labels: list<string>, values: list<int>},
     *     totals: array{revenue: float, orders: int, units: int}
     * }
     */
    public function forPeriod(int $days = 30): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $to = Carbon::now()->endOfDay();
        $from = Carbon::now()->subDays($days - 1)->startOfDay();

        $accepted = Reservation::query()
            ->where('status', Reservation::STATUS_ACCEPTED)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('decided_at', [$from, $to])
                    ->orWhere(function ($inner) use ($from, $to) {
                        $inner->whereNull('decided_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->with('items')
            ->get();

        $items = $accepted->flatMap(fn (Reservation $r) => $r->items);

        return [
            'period' => $days,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'salesTrend' => $this->salesTrend($accepted, $from, $to, $days),
            'topProducts' => $this->topProducts($items, 8),
            'topCustomers' => $this->topCustomers($accepted, 8),
            'topItems' => $this->topItems($items, 8),
            'restock' => $this->restockNeeded(10),
            'totals' => [
                'revenue' => round((float) $items->sum(fn (ReservationItem $i) => (float) $i->line_total), 2),
                'orders' => $accepted->count(),
                'units' => (int) $items->sum('quantity'),
            ],
        ];
    }

    /**
     * @param  Collection<int, Reservation>  $reservations
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function salesTrend(Collection $reservations, Carbon $from, Carbon $to, int $days): array
    {
        $useWeeks = $days >= 90;
        $buckets = [];

        if ($useWeeks) {
            $cursor = $from->copy()->startOfWeek();
            while ($cursor->lte($to)) {
                $key = $cursor->format('o-\\WW');
                $buckets[$key] = 0.0;
                $cursor->addWeek();
            }
        } else {
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $key = $cursor->toDateString();
                $buckets[$key] = 0.0;
                $cursor->addDay();
            }
        }

        foreach ($reservations as $reservation) {
            $date = $reservation->decided_at ?? $reservation->created_at;
            if (! $date) {
                continue;
            }

            $key = $useWeeks ? $date->format('o-\\WW') : $date->toDateString();
            if (! array_key_exists($key, $buckets)) {
                continue;
            }

            $buckets[$key] += (float) $reservation->total;
        }

        $labels = [];
        $values = [];

        foreach ($buckets as $key => $value) {
            $labels[] = $useWeeks
                ? $key
                : Carbon::parse($key)->format($days <= 7 ? 'D d' : 'm-d');
            $values[] = round($value, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param  Collection<int, ReservationItem>  $items
     * @return array{labels: list<string>, quantities: list<int>, revenues: list<float>}
     */
    protected function topProducts(Collection $items, int $limit): array
    {
        $grouped = $items
            ->groupBy(fn (ReservationItem $item) => $item->product_id ?: ('name:'.$item->product_name))
            ->map(function (Collection $group) {
                /** @var ReservationItem $first */
                $first = $group->first();

                return [
                    'label' => $first->product_name ?: __('Product'),
                    'quantity' => (int) $group->sum('quantity'),
                    'revenue' => round((float) $group->sum(fn (ReservationItem $i) => (float) $i->line_total), 2),
                ];
            })
            ->sortByDesc('revenue')
            ->take($limit)
            ->values();

        return [
            'labels' => $grouped->pluck('label')->all(),
            'quantities' => $grouped->pluck('quantity')->all(),
            'revenues' => $grouped->pluck('revenue')->all(),
        ];
    }

    /**
     * @param  Collection<int, Reservation>  $reservations
     * @return array{labels: list<string>, revenues: list<float>, orders: list<int>}
     */
    protected function topCustomers(Collection $reservations, int $limit): array
    {
        $grouped = $reservations
            ->groupBy(function (Reservation $reservation) {
                $phone = trim((string) $reservation->phone);

                return $phone !== '' ? $phone : 'name:'.trim((string) $reservation->customer_name);
            })
            ->map(function (Collection $group) {
                /** @var Reservation $first */
                $first = $group->first();
                $name = trim((string) $first->customer_name) ?: __('Customer');
                $phone = trim((string) $first->phone);

                return [
                    'label' => $phone !== '' ? $name.' ('.$phone.')' : $name,
                    'revenue' => round((float) $group->sum(fn (Reservation $r) => (float) $r->total), 2),
                    'orders' => $group->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->take($limit)
            ->values();

        return [
            'labels' => $grouped->pluck('label')->all(),
            'revenues' => $grouped->pluck('revenue')->all(),
            'orders' => $grouped->pluck('orders')->all(),
        ];
    }

    /**
     * @param  Collection<int, ReservationItem>  $items
     * @return array{labels: list<string>, quantities: list<int>, revenues: list<float>}
     */
    protected function topItems(Collection $items, int $limit): array
    {
        $grouped = $items
            ->groupBy(function (ReservationItem $item) {
                return ($item->product_id ?: '0').':'.($item->product_variant_id ?: '0').':'.$item->product_name.':'.$item->variant_label;
            })
            ->map(function (Collection $group) {
                /** @var ReservationItem $first */
                $first = $group->first();
                $label = $first->product_name ?: __('Item');
                if (trim((string) $first->variant_label) !== '') {
                    $label .= ' — '.$first->variant_label;
                }

                return [
                    'label' => $label,
                    'quantity' => (int) $group->sum('quantity'),
                    'revenue' => round((float) $group->sum(fn (ReservationItem $i) => (float) $i->line_total), 2),
                ];
            })
            ->sortByDesc('quantity')
            ->take($limit)
            ->values();

        return [
            'labels' => $grouped->pluck('label')->all(),
            'quantities' => $grouped->pluck('quantity')->all(),
            'revenues' => $grouped->pluck('revenue')->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    protected function restockNeeded(int $limit): array
    {
        $products = $this->products->lowStock(3, $limit);

        return [
            'labels' => $products->map(fn (Product $p) => $p->t('name') ?: $p->name)->all(),
            'values' => $products->map(fn (Product $p) => (int) $p->available_quantity)->all(),
        ];
    }
}
