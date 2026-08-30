<?php

use App\Models\Shop;
use Illuminate\Support\Facades\Session;

if (! function_exists('current_shop')) {
    function current_shop(): ?Shop
    {
        if (! app()->bound('currentShop')) {
            return null;
        }

        return app('currentShop');
    }
}

if (! function_exists('theme_view')) {
    function theme_view(string $name): string
    {
        $theme = current_shop()?->theme ?? 'flamingo';

        return "themes.{$theme}.{$name}";
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        $symbol = current_shop()?->currency_symbol ?: 'ج.م';

        return __($symbol);
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, 2);

        return $withSymbol ? $formatted.' '.currency_symbol() : $formatted;
    }
}

if (! function_exists('shop_url')) {
    /**
     * Build a shop-aware URL that keeps ?shop= on local/ngrok hosts.
     */
    function shop_url(string $route, mixed $parameters = [], bool $absolute = true): string
    {
        $url = route($route, $parameters, $absolute);

        if (! app()->environment('local')) {
            return $url;
        }

        $shop = current_shop();

        if (! $shop) {
            return $url;
        }

        $host = request()->getHost();
        $isTunnelHost = ! str_ends_with($host, '.test')
            && $host !== 'localhost'
            && $host !== '127.0.0.1';

        $needsShopParam = $isTunnelHost
            || request()->has('shop')
            || Session::has('dev_shop_slug');

        if (! $needsShopParam) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'shop='.urlencode($shop->slug);
    }
}

if (! function_exists('locale_dir')) {
    function locale_dir(): string
    {
        return app()->getLocale() === 'en' ? 'ltr' : 'rtl';
    }
}
