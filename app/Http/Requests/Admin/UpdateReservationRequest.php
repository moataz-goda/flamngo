<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('reservation_items', 'id')->where('reservation_id', $this->route('reservation')),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function itemsPayload(): array
    {
        return collect($this->validated()['items'])
            ->map(fn ($item) => ['id' => (int) $item['id'], 'quantity' => (int) $item['quantity']])
            ->all();
    }
}
