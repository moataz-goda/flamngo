<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function __construct(protected ProductRepositoryInterface $products)
    {
    }

    protected function sessionKey(): string
    {
        $shopId = current_shop()?->id ?? 0;

        return 'cart_'.$shopId;
    }

    protected function lineKey(int $productId, ?int $variantId = null): string
    {
        return $variantId ? $productId.':'.$variantId : (string) $productId;
    }

    public function items(): Collection
    {
        $cart = collect(Session::get($this->sessionKey(), []));

        if ($cart->isEmpty()) {
            return collect();
        }

        $productIds = $cart->map(function ($row, $key) {
            if (is_array($row)) {
                return (int) ($row['product_id'] ?? explode(':', (string) $key)[0]);
            }

            return (int) $key;
        })->unique()->values()->all();

        $products = Product::query()
            ->with(['images', 'variants'])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return $cart->map(function ($row, $key) use ($products) {
            if (is_array($row)) {
                $productId = (int) ($row['product_id'] ?? 0);
                $variantId = isset($row['variant_id']) ? (int) $row['variant_id'] : null;
                $qty = (int) ($row['quantity'] ?? 0);
            } else {
                // Legacy cart shape: [productId => qty]
                $productId = (int) $key;
                $variantId = null;
                $qty = (int) $row;
            }

            $product = $products->get($productId);

            if (! $product || $qty < 1) {
                return null;
            }

            $variant = null;
            $available = $product->available_quantity;
            $unitPrice = $product->current_price;

            if ($variantId) {
                $variant = $product->variants->firstWhere('id', $variantId);

                if (! $variant || ! $variant->is_active) {
                    return null;
                }

                $available = $variant->available_quantity;
                $unitPrice = $variant->unitPriceFor($product);
            } elseif ($product->hasVariants()) {
                return null;
            }

            $quantity = min($qty, max(1, $available));

            return [
                'key' => $this->lineKey($productId, $variantId),
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
            ];
        })->filter()->values();
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function total(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    public function add(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        $product = $this->products->findOrFail($productId);
        $product->load('variants');

        if (! $product->is_active) {
            throw new \RuntimeException(__('This product is not available for reservation.'));
        }

        if ($product->hasVariants()) {
            if (! $variantId) {
                throw new \RuntimeException(__('Please select a variant.'));
            }

            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey($variantId)
                ->where('is_active', true)
                ->first();

            if (! $variant || $variant->available_quantity < 1) {
                throw new \RuntimeException(__('This variant is out of stock.'));
            }

            $available = $variant->available_quantity;
        } else {
            if ($variantId) {
                throw new \RuntimeException(__('Invalid variant.'));
            }

            if ($product->available_quantity < 1) {
                throw new \RuntimeException(__('This product is not available for reservation.'));
            }

            $available = $product->available_quantity;
        }

        $cart = Session::get($this->sessionKey(), []);
        $key = $this->lineKey($productId, $variantId);
        $current = (int) (is_array($cart[$key] ?? null) ? ($cart[$key]['quantity'] ?? 0) : ($cart[$key] ?? 0));
        $newQty = min($current + $quantity, $available);

        if ($newQty < 1) {
            throw new \RuntimeException(__('Out of stock.'));
        }

        $cart[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $newQty,
        ];

        Session::put($this->sessionKey(), $cart);
    }

    public function update(string $lineKey, int $quantity): void
    {
        $cart = Session::get($this->sessionKey(), []);

        if ($quantity < 1) {
            unset($cart[$lineKey]);
            Session::put($this->sessionKey(), $cart);

            return;
        }

        $row = $cart[$lineKey] ?? null;

        if (! is_array($row) && isset($cart[$lineKey])) {
            $row = ['product_id' => (int) $lineKey, 'variant_id' => null, 'quantity' => (int) $cart[$lineKey]];
        }

        if (! is_array($row)) {
            return;
        }

        $product = $this->products->findOrFail((int) $row['product_id']);
        $product->load('variants');
        $variantId = isset($row['variant_id']) ? (int) $row['variant_id'] : null;

        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);
            $available = $variant?->available_quantity ?? 0;
        } else {
            $available = $product->available_quantity;
        }

        $cart[$lineKey] = [
            'product_id' => (int) $row['product_id'],
            'variant_id' => $variantId,
            'quantity' => min($quantity, max(1, $available)),
        ];

        Session::put($this->sessionKey(), $cart);
    }

    public function remove(string $lineKey): void
    {
        $cart = Session::get($this->sessionKey(), []);
        unset($cart[$lineKey]);
        Session::put($this->sessionKey(), $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey());
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }
}
