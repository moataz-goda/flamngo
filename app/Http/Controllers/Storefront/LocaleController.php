<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = $request->string('lang')->toString();

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        $request->session()->put('locale', $locale);

        $previous = url()->previous();
        $root = $request->root();

        // Stay on the current shop host (avoids APP_URL cross-domain CSRF 419).
        if ($previous && str_starts_with($previous, $root)) {
            return redirect()->to($previous);
        }

        return redirect()->to($root.'/');
    }
}
