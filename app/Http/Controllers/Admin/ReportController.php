<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(protected ReservationService $reservations)
    {
    }

    public function index(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $rows = $this->reservations->salesReport($period);

        return view('admin.reports.index', [
            'period' => $period,
            'rows' => $rows,
            'summary' => $this->reservations->salesReportSummary($rows),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $period = $this->resolvePeriod($request);
        $rows = $this->reservations->salesReport($period);
        $filename = 'sales-report-'.$period.'-'.now()->format('Ymd-His').'.xlsx';

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
            __('Note'),
            __('Status'),
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
            $row['note'] ?: '-',
            $row['status'],
        ], $rows);

        $path = (new SimpleXlsxWriter)->writeTemp($headers, $excelRows, __('Sales reports'));

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    protected function resolvePeriod(Request $request): string
    {
        $period = $request->string('period')->toString() ?: 'day';

        if (! in_array($period, ['day', 'week', 'category'], true)) {
            return 'day';
        }

        return $period;
    }
}
