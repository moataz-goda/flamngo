<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReservationRequest;
use App\Services\CartService;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected ReservationService $reservations,
    ) {
    }

    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'أضف هدايا إلى السلة أولاً.');
        }

        return view(theme_view('reservation'), [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        try {
            $reservation = $this->reservations->createFromCart($request->validated());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('reservation.success', $reservation->reference)
            ->with('success', 'تم إرسال طلب الحجز بنجاح.');
    }

    public function success(string $reference): View
    {
        $reservation = $this->reservations->findByReference($reference);

        if (! $reservation) {
            abort(404);
        }

        return view(theme_view('reservation-success'), compact('reservation'));
    }

    public function trackForm(): View
    {
        return view(theme_view('track'));
    }

    public function track(Request $request): View
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $phone = preg_replace('/[\s\-()]/', '', $data['phone']);

        if (preg_match('/^\+?20(1[0125][0-9]{8})$/', $phone, $matches)) {
            $phone = '0'.$matches[1];
        }

        $reservation = $this->reservations->track($data['reference'], $phone);

        return view(theme_view('track'), [
            'reservation' => $reservation,
            'searched' => true,
        ]);
    }
}
