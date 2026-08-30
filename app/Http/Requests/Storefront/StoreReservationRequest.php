<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            // Egyptian mobile: 010/011/012/015 + 8 digits (11 total), or +20 / 20 prefix
            'phone' => ['required', 'string', 'regex:/^(?:\+?20|0)?1[0125][0-9]{8}$/'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'الاسم مطلوب.',
            'phone.required' => 'رقم الموبايل مطلوب.',
            'phone.regex' => 'يرجى إدخال رقم موبايل مصري صحيح (مثال: 01012345678).',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->phone) {
            return;
        }

        $phone = preg_replace('/[\s\-()]/', '', (string) $this->phone);

        // Normalize +20 / 20 to local 0xxxxxxxxx
        if (preg_match('/^\+?20(1[0125][0-9]{8})$/', $phone, $matches)) {
            $phone = '0'.$matches[1];
        }

        $this->merge(['phone' => $phone]);
    }
}
