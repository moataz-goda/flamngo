<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? Session::get('locale')
            ?? config('app.locale', 'ar');

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        if ($request->query('lang')) {
            Session::put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
