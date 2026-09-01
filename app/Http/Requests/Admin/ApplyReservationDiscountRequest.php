<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApplyReservationDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'required_with:discount_type'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('discount_type') === 'percentage' && (float) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', __('A percentage discount cannot exceed 100.'));
            }
        });
    }

    public function discountType(): ?string
    {
        return $this->validated('discount_type') ?: null;
    }

    public function discountValue(): ?float
    {
        $value = $this->validated('discount_value');

        return $value !== null ? (float) $value : null;
    }
}
