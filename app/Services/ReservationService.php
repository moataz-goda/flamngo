<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Models\Governorate;
use App\Models\ProductVariant;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    public function __construct(
        protected ReservationRepositoryInterface $reservations,
        protected ProductRepositoryInterface $products,
        protected CartService $cart,
        protected ActivityLogService $activity,
    ) {
    }

    public function createFromCart(array $customerData): Reservation
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            throw new \RuntimeException(__('Your reservation cart is empty.'));
        }

        return DB::transaction(function () use ($customerData, $items) {
            $lineRows = [];
            $total = 0;

            foreach ($items as $item) {
                $product = $this->products->lockForUpdate($item['product']->id);

                if (! $product || ! $product->is_active) {
                    throw new \RuntimeException(__('One of the products is no longer available.'));
                }

                $variant = $item['variant'] ?? null;
                $variantLabel = null;

                if ($variant) {
                    $lockedVariant = ProductVariant::query()
                        ->whereKey($variant->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedVariant || ! $lockedVariant->is_active) {
                        throw new \RuntimeException(__('One of the variants is no longer available.'));
                    }

                    $available = $lockedVariant->available_quantity;

                    if ($available < $item['quantity']) {
                        throw new \RuntimeException(__('Insufficient stock for :name.', ['name' => $product->name]));
                    }

                    $lockedVariant->reserved_quantity = (int) $lockedVariant->reserved_quantity + $item['quantity'];
                    $lockedVariant->save();

                    $unitPrice = $lockedVariant->unitPriceFor($product);
                    $variantLabel = $lockedVariant->label;
                    $variantId = $lockedVariant->id;
                } else {
                    $available = max(0, (int) $product->stock_quantity - (int) $product->reserved_quantity);

                    if ($available < $item['quantity']) {
                        throw new \RuntimeException(__('Insufficient stock for :name.', ['name' => $product->name]));
                    }

                    $product->reserved_quantity = (int) $product->reserved_quantity + $item['quantity'];
                    $product->save();

                    $unitPrice = (float) ($product->sale_price ?? $product->price);
                    $variantId = null;
                }

                $lineTotal = $unitPrice * $item['quantity'];
                $total += $lineTotal;

                $lineRows[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variantId,
                    'product_name' => $product->name,
                    'variant_label' => $variantLabel,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            $governorate = Governorate::query()->findOrFail($customerData['governorate_id']);

            $reservation = $this->reservations->create([
                'reference' => $this->generateReference(),
                'customer_name' => $customerData['customer_name'],
                'phone' => $customerData['phone'],
                'note' => $customerData['note'] ?? null,
                'status' => Reservation::STATUS_PENDING,
                'governorate_id' => $governorate->id,
                'address' => $customerData['address'],
                'shipping_cost' => $governorate->shipping_cost,
                'total' => $total + (float) $governorate->shipping_cost,
            ]);

            foreach ($lineRows as $row) {
                $reservation->items()->create($row);
            }

            $this->cart->clear();

            return $reservation->load('items');
        });
    }

    public function accept(int $id, ?string $adminNote = null): Reservation
    {
        return DB::transaction(function () use ($id, $adminNote) {
            $reservation = Reservation::query()->with('items')->lockForUpdate()->findOrFail($id);

            if (! $reservation->isPending()) {
                throw new \RuntimeException(__('A decision was already made for this reservation.'));
            }

            foreach ($reservation->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first();

                    if ($variant) {
                        $variant->stock_quantity = max(0, (int) $variant->stock_quantity - (int) $item->quantity);
                        $variant->reserved_quantity = max(0, (int) $variant->reserved_quantity - (int) $item->quantity);
                        $variant->save();
                    }

                    continue;
                }

                if (! $item->product_id) {
                    continue;
                }

                $product = $this->products->lockForUpdate($item->product_id);

                if (! $product) {
                    continue;
                }

                $product->stock_quantity = max(0, (int) $product->stock_quantity - (int) $item->quantity);
                $product->reserved_quantity = max(0, (int) $product->reserved_quantity - (int) $item->quantity);
                $product->save();
            }

            $reservation->update([
                'status' => Reservation::STATUS_ACCEPTED,
                'admin_note' => $adminNote,
                'decided_at' => now(),
                'decided_by' => auth()->id(),
            ]);

            $this->activity->log(
                'reservation.accepted',
                $reservation,
                __('Accepted reservation :ref', ['ref' => $reservation->reference]),
                ['admin_note' => $adminNote]
            );

            return $reservation->fresh(['items', 'decidedByUser']);
        });
    }

    public function reject(int $id, ?string $adminNote = null): Reservation
    {
        return DB::transaction(function () use ($id, $adminNote) {
            $reservation = Reservation::query()->with('items')->lockForUpdate()->findOrFail($id);

            if (! $reservation->isPending()) {
                throw new \RuntimeException(__('A decision was already made for this reservation.'));
            }

            foreach ($reservation->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first();

                    if ($variant) {
                        $variant->reserved_quantity = max(0, (int) $variant->reserved_quantity - (int) $item->quantity);
                        $variant->save();
                    }

                    continue;
                }

                if (! $item->product_id) {
                    continue;
                }

                $product = $this->products->lockForUpdate($item->product_id);

                if (! $product) {
                    continue;
                }

                $product->reserved_quantity = max(0, (int) $product->reserved_quantity - (int) $item->quantity);
                $product->save();
            }

            $reservation->update([
                'status' => Reservation::STATUS_REJECTED,
                'admin_note' => $adminNote,
                'decided_at' => now(),
                'decided_by' => auth()->id(),
            ]);

            $this->activity->log(
                'reservation.rejected',
                $reservation,
                __('Rejected reservation :ref', ['ref' => $reservation->reference]),
                ['admin_note' => $adminNote]
            );

            return $reservation->fresh(['items', 'decidedByUser']);
        });
    }

    /**
     * @param  list<array{id: int, quantity: int}>  $items
     */
    public function updateItems(int $id, array $items): Reservation
    {
        return DB::transaction(function () use ($id, $items) {
            $reservation = Reservation::query()->with('items')->lockForUpdate()->findOrFail($id);

            if (! $reservation->isPending()) {
                throw new \RuntimeException(__('Only pending reservations can be edited.'));
            }

            if (empty($items)) {
                throw new \RuntimeException(__('A reservation must have at least one product.'));
            }

            $keptQuantities = collect($items)->pluck('quantity', 'id');
            $total = 0;

            foreach ($reservation->items as $item) {
                $newQuantity = $keptQuantities->get($item->id);

                if ($newQuantity === null) {
                    $this->adjustReservedQuantity($item, -$item->quantity);
                    $item->delete();

                    continue;
                }

                $delta = $newQuantity - $item->quantity;

                if ($delta !== 0) {
                    $this->adjustReservedQuantity($item, $delta);
                }

                $lineTotal = (float) $item->unit_price * $newQuantity;

                $item->update([
                    'quantity' => $newQuantity,
                    'line_total' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $discount = $this->discountAmountFor($total, $reservation->discount_type, $reservation->discount_value);
            $reservation->update(['total' => max(0, $total - $discount) + (float) $reservation->shipping_cost]);

            $this->activity->log(
                'reservation.items_updated',
                $reservation,
                __('Updated items for reservation :ref', ['ref' => $reservation->reference]),
                ['items' => $items]
            );

            return $reservation->fresh(['items', 'decidedByUser']);
        });
    }

    public function applyDiscount(int $id, ?string $type, ?float $value): Reservation
    {
        return DB::transaction(function () use ($id, $type, $value) {
            $reservation = Reservation::query()->with('items')->lockForUpdate()->findOrFail($id);

            if (! $reservation->isPending()) {
                throw new \RuntimeException(__('Only pending reservations can be edited.'));
            }

            $type = $type ?: null;
            $value = $type ? max(0, (float) $value) : 0;

            if ($type === 'percentage') {
                $value = min($value, 100);
            }

            $subtotal = (float) $reservation->items->sum('line_total');
            $discount = $this->discountAmountFor($subtotal, $type, $value);

            $reservation->update([
                'discount_type' => $type,
                'discount_value' => $value,
                'total' => max(0, $subtotal - $discount) + (float) $reservation->shipping_cost,
            ]);

            $this->activity->log(
                'reservation.discount_applied',
                $reservation,
                $type
                    ? __('Applied discount to reservation :ref', ['ref' => $reservation->reference])
                    : __('Removed discount from reservation :ref', ['ref' => $reservation->reference]),
                ['discount_type' => $type, 'discount_value' => $value]
            );

            return $reservation->fresh(['items', 'decidedByUser']);
        });
    }

    protected function discountAmountFor(float $subtotal, ?string $type, $value): float
    {
        $value = (float) ($value ?? 0);

        $amount = match ($type) {
            'percentage' => $subtotal * $value / 100,
            'fixed' => $value,
            default => 0.0,
        };

        return min(max(0, $amount), $subtotal);
    }

    /**
     * Adjust reserved_quantity on the item's variant/product by $delta,
     * validating available stock when the delta increases the reservation.
     */
    protected function adjustReservedQuantity(ReservationItem $item, int $delta): void
    {
        if ($item->product_variant_id) {
            $variant = ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first();

            if (! $variant) {
                return;
            }

            if ($delta > 0 && $variant->available_quantity < $delta) {
                throw new \RuntimeException(__('Insufficient stock for :name.', ['name' => $item->product_name]));
            }

            $variant->reserved_quantity = max(0, (int) $variant->reserved_quantity + $delta);
            $variant->save();

            return;
        }

        if (! $item->product_id) {
            return;
        }

        $product = $this->products->lockForUpdate($item->product_id);

        if (! $product) {
            return;
        }

        if ($delta > 0 && $product->available_quantity < $delta) {
            throw new \RuntimeException(__('Insufficient stock for :name.', ['name' => $item->product_name]));
        }

        $product->reserved_quantity = max(0, (int) $product->reserved_quantity + $delta);
        $product->save();
    }

    public function findByReference(string $reference)
    {
        return $this->reservations->findByReference($reference);
    }

    public function track(string $reference, string $phone)
    {
        return $this->reservations->findByReferenceAndPhone($reference, $phone);
    }

    public function filterByStatus(?string $status = null, int $perPage = 15)
    {
        return $this->reservations->filterByStatus($status, $perPage);
    }

    public function find(int $id)
    {
        return $this->reservations->findOrFail($id)->load([
            'items.product.category',
            'items.variant',
            'decidedByUser',
            'governorate',
        ]);
    }

    public function stats(): array
    {
        return $this->reservations->stats();
    }

    public function recent(int $limit = 5)
    {
        return $this->reservations->recent($limit);
    }

    public function pendingCount(): int
    {
        return $this->reservations->pendingCount();
    }

    public function exportRows(?string $status = null): array
    {
        $query = Reservation::query()
            ->with(['items.product.category'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $rows = [];

        foreach ($query->get() as $reservation) {
            foreach ($reservation->items as $item) {
                $rows[] = [
                    'date' => $reservation->created_at?->format('Y-m-d H:i') ?? '',
                    'reference' => $reservation->reference,
                    'customer_name' => $reservation->customer_name,
                    'phone' => $reservation->phone,
                    'note' => $reservation->note,
                    'product' => $item->product_name,
                    'variant' => $item->variant_label,
                    'category' => $item->product?->category?->t('name')
                        ?? $item->product?->category?->name
                        ?? __('Uncategorized'),
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'reservation_total' => (float) $reservation->total,
                    'status' => $reservation->status_label,
                    'decided_at' => $reservation->decided_at?->format('Y-m-d H:i') ?? '',
                    'admin_note' => $reservation->admin_note,
                ];
            }
        }

        return $rows;
    }

    public function salesReport(string $period = 'day'): array
    {
        $accepted = Reservation::query()
            ->where('status', Reservation::STATUS_ACCEPTED)
            ->with('items.product.category')
            ->latest('decided_at')
            ->latest('id');

        $rows = [];

        foreach ($accepted->get() as $reservation) {
            $date = $reservation->decided_at ?? $reservation->created_at;
            $periodKey = match ($period) {
                'week' => $date?->format('o-\\WW') ?? '',
                'category' => 'category',
                default => $date?->format('Y-m-d') ?? '',
            };

            foreach ($reservation->items as $item) {
                $category = $item->product?->category?->t('name')
                    ?? $item->product?->category?->name
                    ?? __('Uncategorized');

                if ($period === 'category') {
                    $periodKey = $category;
                }

                $rows[] = [
                    'period_key' => $periodKey,
                    'date' => $date?->format('Y-m-d H:i') ?? '',
                    'reference' => $reservation->reference,
                    'customer_name' => $reservation->customer_name,
                    'phone' => $reservation->phone,
                    'note' => $reservation->note,
                    'product' => $item->product_name,
                    'variant' => $item->variant_label,
                    'category' => $category,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'reservation_total' => (float) $reservation->total,
                    'status' => $reservation->status_label,
                ];
            }
        }

        if ($period === 'category') {
            usort($rows, fn ($a, $b) => [$a['category'], $a['date']] <=> [$b['category'], $b['date']]);
        } elseif ($period === 'week') {
            usort($rows, fn ($a, $b) => [$b['period_key'], $b['date']] <=> [$a['period_key'], $a['date']]);
        }

        return $rows;
    }

    /**
     * @return array{reservations: int, items: int, total: float}
     */
    public function salesReportSummary(array $rows): array
    {
        $reservationRefs = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $reservationRefs[$row['reference']] = true;
            $total += (float) $row['line_total'];
        }

        return [
            'reservations' => count($reservationRefs),
            'items' => count($rows),
            'total' => $total,
        ];
    }

    protected function generateReference(): string
    {
        $prefix = current_shop()?->reference_prefix ?: 'FLM';

        do {
            $reference = $prefix.'-'.Str::upper(Str::random(6));
        } while ($this->reservations->findByReference($reference));

        return $reference;
    }
}
