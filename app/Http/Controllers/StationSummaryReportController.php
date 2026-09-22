<?php

namespace App\Http\Controllers;

use App\Services\StationReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationSummaryReportController extends Controller
{
    public function __construct(private StationReportService $reportService)
    {
    }

    public function index()
    {
        $stations = Auth::user()->stations;
        return view('station_summary', compact('stations'));
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
        $unmatchedCount = $this->reportService->unmatchedDeliveriesCount($stationIds, $start_date, $end_date);

        return view('station_summary_result', compact('stations', 'fuelByType', 'stationSummaries', 'unmatchedCount', 'start_date', 'end_date'));
    }
}