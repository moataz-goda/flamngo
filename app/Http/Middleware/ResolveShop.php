<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveShop
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        $shop = Shop::query()
            ->where('is_active', true)
            ->where(function ($query) use ($host) {
                $query->where('domain', $host)
                    ->orWhere('domain', 'www.'.$host);
            })
            ->first();

        // Local/ngrok: allow ?shop=bubbles|flamingo when the public host is unfamiliar.
        // Remember the choice in session so navigation keeps the same shop.
        if (! $shop && app()->environment('local')) {
            $shopKey = strtolower((string) $request->query('shop', ''));

            if ($shopKey !== '') {
                $shop = Shop::query()
                    ->where('is_active', true)
                    ->where(function ($query) use ($shopKey) {
                        $query->where('slug', $shopKey)
                            ->orWhere('theme', $shopKey);
                    })
                    ->first();

                if ($shop) {
                    $request->session()->put('dev_shop_slug', $shop->slug);
                }
            }

            if (! $shop) {
                $savedSlug = $request->session()->get('dev_shop_slug');

                if (is_string($savedSlug) && $savedSlug !== '') {
                    $shop = Shop::query()
                        ->where('is_active', true)
                        ->where('slug', $savedSlug)
                        ->first();
                }
            }

            if (! $shop && str_contains($host, 'bubbles')) {
                $shop = Shop::query()->where('theme', 'bubbles')->where('is_active', true)->first();
            }

            if (! $shop) {
                $shop = Shop::query()
                    ->where('is_active', true)
                    ->where('domain', 'flamingo.test')
                    ->first();
            }
        }

        if (! $shop) {
            abort(404, 'المتجر غير موجود.');
        }

        app()->instance('currentShop', $shop);
        View::share('shop', $shop);

        return $next($request);
    }
}
