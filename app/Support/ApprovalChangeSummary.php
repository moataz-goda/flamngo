<?php

namespace App\Support;

use App\Models\ApprovalRequest;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;

/**
 * Turns approval payloads into end-user readable change rows.
 */
class ApprovalChangeSummary
{
    /**
     * @return list<array{label: string, value: string, previous?: string|null}>
     */
    public function rows(ApprovalRequest $approval): array
    {
        $payload = $approval->payload ?? [];
        $subject = $this->resolveSubject($approval);

        return match ($approval->action) {
            'banner.create', 'banner.update' => $this->entityFields(
                $payload['data'] ?? [],
                $subject instanceof Banner ? $subject : null,
                $this->bannerLabels(),
                $payload
            ),
            'banner.delete' => $this->deleteRows($subject, $payload, __('Banner')),
            'category.create', 'category.update' => $this->entityFields(
                $payload['data'] ?? [],
                $subject instanceof Category ? $subject : null,
                $this->categoryLabels(),
                $payload
            ),
            'category.delete' => $this->deleteRows($subject, $payload, __('Category')),
            'product.create', 'product.update' => array_merge(
                $this->entityFields(
                    $payload['data'] ?? [],
                    $subject instanceof Product ? $subject : null,
                    $this->productLabels(),
                    $payload
                ),
                $this->variantRows($payload['variants'] ?? [])
            ),
            'product.delete' => $this->deleteRows($subject, $payload, __('Product')),
            'product.delete_image' => $this->deleteImageRows($payload),
            'product.import' => [[
                'label' => __('Excel file'),
                'value' => ! empty($payload['excel_path'] ?? $payload['csv_path'] ?? null)
                    ? __('Attached for import')
                    : __('—'),
            ]],
            'reservation.accept', 'reservation.reject' => $this->reservationRows($payload, $subject),
            default => $this->fallbackRows($payload),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $labels
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, value: string, previous?: string|null}>
     */
    protected function entityFields(array $data, ?object $current, array $labels, array $payload): array
    {
        $rows = [];

        foreach ($labels as $key => $label) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $newRaw = $data[$key];
            $oldRaw = $current ? ($current->{$key} ?? null) : null;
            $newValue = $this->formatValue($key, $newRaw);
            $oldValue = $current ? $this->formatValue($key, $oldRaw) : null;
            $isEmpty = $newValue === __('—') || trim((string) $newValue) === '';

            if (! $current && $isEmpty) {
                continue;
            }

            if ($current && (string) $newValue === (string) $oldValue) {
                continue;
            }

            $row = [
                'label' => $label,
                'value' => $newValue,
            ];

            if ($current && $oldValue !== null && $oldValue !== __('—') && $oldValue !== $newValue) {
                $row['previous'] = $oldValue;
            }

            $rows[] = $row;
        }

        $imageNote = $this->imageAttachmentNote($payload);
        if ($imageNote !== null) {
            $rows[] = [
                'label' => __('Image'),
                'value' => $imageNote,
            ];
        }

        if ($rows === [] && $current) {
            $rows[] = [
                'label' => __('Summary'),
                'value' => __('No field changes detected (may be image-only or identical values).'),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, value: string}>
     */
    protected function deleteRows(?object $subject, array $payload, string $typeLabel): array
    {
        $name = $this->subjectName($subject);

        return [
            [
                'label' => $typeLabel,
                'value' => $name ?? ('#'.($payload['id'] ?? '—')),
            ],
            [
                'label' => __('Requested change'),
                'value' => __('Delete'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, value: string}>
     */
    protected function deleteImageRows(array $payload): array
    {
        $imageId = (int) ($payload['image_id'] ?? 0);
        $image = $imageId > 0 ? ProductImage::query()->with('product')->find($imageId) : null;

        return [
            [
                'label' => __('Product'),
                'value' => $image?->product?->t('name') ?? __('—'),
            ],
            [
                'label' => __('Requested change'),
                'value' => __('Delete product image'),
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     * @return list<array{label: string, value: string}>
     */
    protected function variantRows(array $variants): array
    {
        if ($variants === []) {
            return [];
        }

        $lines = [];
        foreach ($variants as $index => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $parts = array_filter([
                trim((string) ($variant['name'] ?? '')),
                isset($variant['sku']) && $variant['sku'] !== '' ? 'SKU: '.$variant['sku'] : null,
                isset($variant['price']) && $variant['price'] !== '' && $variant['price'] !== null
                    ? __('Price').': '.$this->formatMoney($variant['price'])
                    : null,
                isset($variant['stock_quantity']) ? __('Stock').': '.(int) $variant['stock_quantity'] : null,
            ]);

            if ($parts === []) {
                continue;
            }

            $lines[] = ($index + 1).'. '.implode(' · ', $parts);
        }

        if ($lines === []) {
            return [];
        }

        return [[
            'label' => __('Variants'),
            'value' => implode("\n", $lines),
        ]];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, value: string}>
     */
    protected function reservationRows(array $payload, ?object $subject): array
    {
        $reservation = $subject instanceof Reservation
            ? $subject
            : Reservation::query()->find((int) ($payload['reservation_id'] ?? 0));

        $rows = [
            [
                'label' => __('Reservation'),
                'value' => $reservation
                    ? '#'.$reservation->id.($reservation->customer_name ? ' — '.$reservation->customer_name : '')
                    : '#'.($payload['reservation_id'] ?? '—'),
            ],
        ];

        if ($reservation?->total !== null) {
            $rows[] = [
                'label' => __('Total'),
                'value' => $this->formatMoney($reservation->total),
            ];
        }

        $note = trim((string) ($payload['admin_note'] ?? ''));
        if ($note !== '') {
            $rows[] = [
                'label' => __('Admin note'),
                'value' => $note,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, value: string}>
     */
    protected function fallbackRows(array $payload): array
    {
        $rows = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, ['data', 'image_path', 'image_paths', 'excel_path', 'csv_path', 'variants'], true)) {
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            $rows[] = [
                'label' => $this->fieldLabel((string) $key),
                'value' => $this->formatValue((string) $key, $value),
            ];
        }

        return $rows !== [] ? $rows : [[
            'label' => __('Summary'),
            'value' => __('No extra details available.'),
        ]];
    }

    protected function resolveSubject(ApprovalRequest $approval): ?object
    {
        try {
            if ($approval->relationLoaded('subject') && $approval->subject) {
                return $approval->subject;
            }

            if ($approval->subject_type && $approval->subject_id) {
                return $approval->subject()->first();
            }

            $payload = $approval->payload ?? [];
            $id = (int) ($payload['id'] ?? $payload['reservation_id'] ?? 0);

            if ($id < 1) {
                return null;
            }

            return match (true) {
                str_starts_with($approval->action, 'banner.') => Banner::query()->find($id),
                str_starts_with($approval->action, 'category.') => Category::query()->find($id),
                str_starts_with($approval->action, 'product.') => Product::query()->find($id),
                str_starts_with($approval->action, 'reservation.') => Reservation::query()->find($id),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    protected function subjectName(?object $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        if (method_exists($subject, 't')) {
            foreach (['name', 'title'] as $field) {
                $value = $subject->t($field);
                if (is_string($value) && trim($value) !== '') {
                    return $value;
                }
            }
        }

        foreach (['name', 'title', 'customer_name'] as $field) {
            if (! empty($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }

        return isset($subject->id) ? '#'.$subject->id : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function imageAttachmentNote(array $payload): ?string
    {
        if (! empty($payload['image_path']) && is_string($payload['image_path'])) {
            return Storage::disk('public')->exists($payload['image_path'])
                ? __('New image attached')
                : __('Image path stored');
        }

        $paths = $payload['image_paths'] ?? [];
        if (is_array($paths) && $paths !== []) {
            $count = count(array_filter($paths));

            return $count === 1
                ? __('1 new image attached')
                : __(':count new images attached', ['count' => $count]);
        }

        return null;
    }

    protected function formatValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('—');
        }

        if (in_array($key, ['is_active', 'is_featured'], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? __('Yes') : __('No');
        }

        if ($key === 'category_id') {
            try {
                $category = Category::query()->find((int) $value);

                return $category?->t('name') ?? ('#'.$value);
            } catch (\Throwable) {
                return '#'.$value;
            }
        }

        if (in_array($key, ['price', 'sale_price'], true)) {
            return $this->formatMoney($value);
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: __('—');
        }

        return (string) $value;
    }

    protected function formatMoney(mixed $value): string
    {
        if (! is_numeric($value)) {
            return (string) $value;
        }

        return function_exists('money')
            ? money((float) $value)
            : number_format((float) $value, 2);
    }

    protected function fieldLabel(string $key): string
    {
        return $this->bannerLabels()[$key]
            ?? $this->categoryLabels()[$key]
            ?? $this->productLabels()[$key]
            ?? __(str_replace('_', ' ', ucfirst($key)));
    }

    /**
     * @return array<string, string>
     */
    protected function bannerLabels(): array
    {
        return [
            'title' => __('Title').' (AR)',
            'title_en' => __('Title').' (EN)',
            'subtitle' => __('Subtitle').' (AR)',
            'subtitle_en' => __('Subtitle').' (EN)',
            'button_text' => __('Button text').' (AR)',
            'button_text_en' => __('Button text').' (EN)',
            'button_url' => __('Button URL'),
            'sort_order' => __('Sort order'),
            'is_active' => __('Active'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function categoryLabels(): array
    {
        return [
            'name' => __('Name').' (AR)',
            'name_en' => __('Name').' (EN)',
            'description' => __('Description').' (AR)',
            'description_en' => __('Description').' (EN)',
            'sort_order' => __('Sort order'),
            'is_active' => __('Active'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productLabels(): array
    {
        return [
            'category_id' => __('Category'),
            'name' => __('Name').' (AR)',
            'name_en' => __('Name').' (EN)',
            'short_description' => __('Short description').' (AR)',
            'short_description_en' => __('Short description').' (EN)',
            'description' => __('Description').' (AR)',
            'description_en' => __('Description').' (EN)',
            'price' => __('Price'),
            'sale_price' => __('Sale price'),
            'stock_quantity' => __('Stock'),
            'sku' => __('SKU'),
            'brand' => __('Brand'),
            'color' => __('Color'),
            'material' => __('Material'),
            'size' => __('Size'),
            'is_featured' => __('Featured'),
            'is_active' => __('Active'),
        ];
    }
}
