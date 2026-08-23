<?php

namespace App\Http\Controllers;

use App\Models\DepositDetail;
use App\Models\ExpenseDetail;
use App\Models\MachineDetail;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;

class StationHubController extends Controller
{
    public function show(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }

        // إحصائيات سريعة لليوم
        $today = now()->toDateString();

        $stats = [
            'deposits'   => DepositDetail::where('station_id', $station->id)->whereDate('date', $today)->sum('deposit_amount'),
            'expenses'   => ExpenseDetail::where('station_id', $station->id)->whereDate('date', $today)->sum('expense_amount'),
            'readings'   => MachineDetail::where('station_id', $station->id)
                ->whereDate('date', $today)
                ->where(function ($q) {
                    $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending');
                })
                ->sum('net'),
            'pending_approvals' => MachineDetail::where('station_id', $station->id)
                ->where('approval_status', 'pending')
                ->count(),
        ];

        return view('stations.hub', compact('station', 'stats'));
    }
}
