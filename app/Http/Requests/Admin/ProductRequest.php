<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'name_en' => ['nullable', 'string', 'max:160'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'short_description_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:60'],
            'brand' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:80'],
            'color_en' => ['nullable', 'string', 'max:80'],
            'material' => ['nullable', 'string', 'max:80'],
            'material_en' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:80'],
            'size_en' => ['nullable', 'string', 'max:80'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['nullable', 'string', 'max:60'],
            'variants.*.size' => ['nullable', 'string', 'max:80'],
            'variants.*.size_en' => ['nullable', 'string', 'max:80'],
            'variants.*.color' => ['nullable', 'string', 'max:80'],
            'variants.*.color_en' => ['nullable', 'string', 'max:80'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'sale_price' => $this->sale_price === '' ? null : $this->sale_price,
        ]);

        $images = $this->file('images');

        if (is_array($images)) {
            $this->files->set('images', array_values(array_filter($images)));
        }
    }

    public function uploadedImages(): array
    {
        return array_values(array_filter((array) $this->file('images', [])));
    }

    public function variantsPayload(): array
    {
        return array_values(array_filter((array) $this->input('variants', []), function ($variant) {
            if (! is_array($variant)) {
                return false;
            }

            return trim((string) ($variant['sku'] ?? '')) !== ''
                || trim((string) ($variant['size'] ?? '')) !== ''
                || trim((string) ($variant['color'] ?? '')) !== '';
        }));
    }

    public function productPayload(): array
    {
        return collect($this->validated())
            ->except(['images', 'variants'])
            ->all();
    }
}
