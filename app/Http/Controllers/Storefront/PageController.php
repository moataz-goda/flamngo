<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view(theme_view('about'), [
            'shop' => current_shop(),
        ]);
    }

    public function contact(): View
    {
        return view(theme_view('contact'), [
            'shop' => current_shop(),
        ]);
    }
}
