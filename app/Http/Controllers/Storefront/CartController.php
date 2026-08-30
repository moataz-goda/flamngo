<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index(): View
    {
        return view(theme_view('cart'), [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $this->cart->add(
                (int) $data['product_id'],
                (int) ($data['quantity'] ?? 1),
                isset($data['variant_id']) ? (int) $data['variant_id'] : null
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('success', __('Added to reservation cart.'));
    }

    public function update(Request $request, string $lineKey): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $this->cart->update($lineKey, (int) $data['quantity']);

        return back()->with('success', __('Cart updated.'));
    }

    public function destroy(string $lineKey): RedirectResponse
    {
        $this->cart->remove($lineKey);

        return back()->with('success', __('Removed from cart.'));
    }
}
