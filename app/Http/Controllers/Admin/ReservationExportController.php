<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReservationExportController extends Controller
{
    public function __construct(protected ReservationService $reservations)
    {
    }

    public function __invoke(Request $request): BinaryFileResponse
    {
        $status = $request->string('status')->toString() ?: null;
        $rows = $this->reservations->exportRows($status ?: null);
        $filename = 'reservations-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.xlsx';

        $headers = [
            __('Date'),
            __('Reference'),
            __('Customer'),
            __('Mobile'),
            __('Product'),
            __('Variant'),
            __('Category'),
            __('Quantity'),
            __('Unit price'),
            __('Line total'),
            __('Reservation total'),
            __('Status'),
            __('Customer note'),
            __('Decision made on'),
            __('Admin note'),
        ];

        $excelRows = array_map(fn (array $row) => [
            $row['date'],
            $row['reference'],
            $row['customer_name'],
            $row['phone'],
            $row['product'],
            $row['variant'] ?: '-',
            $row['category'],
            $row['quantity'],
            $row['unit_price'],
            $row['line_total'],
            $row['reservation_total'],
            $row['status'],
            $row['note'] ?: '-',
            $row['decided_at'] ?: '-',
            $row['admin_note'] ?: '-',
        ], $rows);

        $path = (new SimpleXlsxWriter)->writeTemp($headers, $excelRows, __('Reservations'));

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
