<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GovernorateController extends Controller
{
    public function edit(): View
    {
        return view('admin.governorates.edit', [
            'governorates' => Governorate::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('governorates', 'name')->where('shop_id', current_shop()?->id),
            ],
            'name_en' => ['nullable', 'string', 'max:120'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
        ]);

        Governorate::query()->create([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'shipping_cost' => $data['shipping_cost'],
            'sort_order' => ((int) Governorate::query()->max('sort_order')) + 1,
        ]);

        return back()->with('success', __('Governorate added.'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'costs' => ['required', 'array'],
            'costs.*' => ['required', 'numeric', 'min:0'],
        ]);

        $governorates = Governorate::query()->whereKey(array_keys($data['costs']))->get();

        foreach ($governorates as $governorate) {
            $governorate->update(['shipping_cost' => $data['costs'][$governorate->id]]);
        }

        return back()->with('success', __('Shipping costs updated.'));
    }
}
