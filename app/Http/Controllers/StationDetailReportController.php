<?php

namespace App\Http\Controllers;

use App\Services\StationReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationDetailReportController extends Controller
{
    public function __construct(private StationReportService $reportService)
    {
    }

    public function index()
    {
        $stations = Auth::user()->stations;
        return view('station_detail', compact('stations'));
    }

    public function result(Request $request)
    {
        $validated = $request->validate([
            'station_ids'   => ['required', 'array', 'min:1'],
            'station_ids.*' => ['integer'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $stationIds = array_map('intval', $validated['station_ids']);
        $start_date = $validated['start_date'] ?? null;
        $end_date = $validated['end_date'] ?? null;

        $stations = $this->reportService->stationsByIds($stationIds);
        $fuelByType = $this->reportService->fuelReceivedByType($stationIds, $start_date, $end_date);
        $stationSummaries = $this->reportService->stationSummaries($stationIds, $start_date, $end_date);
        $deliveries = $this->reportService->deliveries($stationIds, $start_date, $end_date);
        $expenseDetails = $this->reportService->expenseDetails($stationIds, $start_date, $end_date);
        $salesDetails = $this->reportService->salesDetails($stationIds, $start_date, $end_date);

        // تُحفظ مع روابط الترقيم حتى لا تضيع الفلاتر عند الانتقال لصفحة أخرى
        $appends = $request->except(['_token', 'page']);

        return view('station_detail_result', compact(
            'stations',
            'fuelByType',
            'stationSummaries',
            'deliveries',
            'expenseDetails',
            'salesDetails',
            'appends',
            'start_date',
            'end_date'
        ));
    }
}