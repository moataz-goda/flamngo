<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ThemePalette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function edit(): View
    {
        $shop = current_shop();

        return view('admin.settings.shop', [
            'shop' => $shop,
            'colors' => $shop->effectiveColors(),
            'defaultColors' => ThemePalette::defaults($shop->theme),
            'colorFields' => ThemePalette::fields(),
            'hasCustomColors' => $shop->hasCustomColors(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = current_shop();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:120', Rule::unique('shops', 'domain')->ignore($shop->id)],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'colors' => ['nullable', 'array'],
            'colors.*' => ['nullable', 'string', 'max:7'],
        ]);

        $colorsInput = $request->input('colors', []);
        $overrides = [];
        $defaults = ThemePalette::defaults($shop->theme);

        foreach (ThemePalette::keys() as $key) {
            $normalized = ThemePalette::normalizeHex($colorsInput[$key] ?? null);
            if ($normalized === null) {
                continue;
            }

            // Store only values that differ from the theme default.
            if (strcasecmp($normalized, $defaults[$key] ?? '') !== 0) {
                $overrides[$key] = $normalized;
            }
        }

        $invalid = collect($colorsInput)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '' && ! ThemePalette::isValidHex($value));

        if ($invalid->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['colors' => __('Please enter valid hex colors like #3D0A4B.')]);
        }

        $data['brand_colors'] = $overrides === [] ? null : $overrides;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('shops', 'public');
        } else {
            unset($data['logo']);
        }

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('shops', 'public');
        } else {
            unset($data['hero_image']);
        }

        unset($data['colors'], $data['theme'], $data['default_theme']);

        $shop->update($data);
        app()->instance('currentShop', $shop->fresh());

        return back()->with('success', __('Shop settings saved.'));
    }

    public function restoreColors(): RedirectResponse
    {
        $shop = current_shop();
        $shop->update(['brand_colors' => null]);
        app()->instance('currentShop', $shop->fresh());

        return back()->with('success', __('Default colors restored.'));
    }
}
