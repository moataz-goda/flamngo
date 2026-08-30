<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservationRequest;
use App\Models\Reservation;
use App\Services\AuthorizationService;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservations,
        protected AuthorizationService $authorizer,
    ) {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;

        return view('admin.reservations.index', [
            'reservations' => $this->reservations->filterByStatus($status),
            'status' => $status,
            'stats' => $this->reservations->stats(),
        ]);
    }

    public function show(int $reservation): View
    {
        return view('admin.reservations.show', [
            'reservation' => $this->reservations->find($reservation),
        ]);
    }

    public function update(UpdateReservationRequest $request, int $reservation): RedirectResponse
    {
        $items = $request->itemsPayload();

        try {
            return $this->authorizer->runOrQueue(
                $request->user(),
                'reservations.decide',
                'reservation.update_items',
                ['reservation_id' => $reservation, 'items' => $items],
                function () use ($reservation, $items) {
                    $this->reservations->updateItems($reservation, $items);
                },
                Reservation::class,
                $reservation,
                __('Reservation updated and stock adjusted.'),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function accept(Request $request, int $reservation): RedirectResponse
    {
        $note = $request->string('admin_note')->toString() ?: null;

        return $this->authorizer->runOrQueue(
            $request->user(),
            'reservations.decide',
            'reservation.accept',
            ['reservation_id' => $reservation, 'admin_note' => $note],
            function () use ($reservation, $note) {
                $this->reservations->accept($reservation, $note);
            },
            Reservation::class,
            $reservation,
            __('Reservation accepted and stock updated.'),
        );
    }

    public function reject(Request $request, int $reservation): RedirectResponse
    {
        $note = $request->string('admin_note')->toString() ?: null;

        return $this->authorizer->runOrQueue(
            $request->user(),
            'reservations.decide',
            'reservation.reject',
            ['reservation_id' => $reservation, 'admin_note' => $note],
            function () use ($reservation, $note) {
                $this->reservations->reject($reservation, $note);
            },
            Reservation::class,
            $reservation,
            __('Reservation rejected and reserved stock released.'),
        );
    }
}
