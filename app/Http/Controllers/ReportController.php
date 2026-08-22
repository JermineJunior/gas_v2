<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DepositDetail;
use App\Models\Detail;
use App\Models\FuelOrder;
use App\Models\MachineDetail;
use App\Models\Station;
use App\Models\Supplier;
use App\Models\Tuncker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function deposit_detail()
    {
        $stations = Auth::user()->stations;
        return view('deposit_detail', compact('stations'));
    }

    public function deposit_detail_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = DepositDetail::with('deposit.employee')
            ->when($request->station_id, function ($query) use ($request) {
                return $query->where('station_id', $request->station_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('deposit_detail_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function machine_detail()
    {
        $stations = Auth::user()->stations;
        return view('machine_detail', compact('stations'));
    }

    public function machine_detail_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = MachineDetail::when($request->station_id, function ($query) use ($request) {
            return $query->where('station_id', $request->station_id);
        })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('machine_detail_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function tuncker()
    {
        $stations = Auth::user()->stations;
        return view('tuncker_detail', compact('stations'));
    }

    public function tuncker_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = Tuncker::when($request->station_id, function ($query) use ($request) {
            return $query->where('station_id', $request->station_id);
        })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('tuncker_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function supplier()
    {
        $suppliers = Supplier::get();
        return view('supplier_detail', compact('suppliers'));
    }

    public function supplier_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $supplier = Supplier::find($request->supplier_id) ?? null;
        $operations = FuelOrder::with(['supplier' => function ($q) {
            $q->withSum('fuelOrders as total_orders', 'quantity')
                ->withSum('fuelDeliveries as total_deliveries', 'quantity');
        }])
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('supplier_result', compact('operations', 'start_date', 'end_date', 'supplier'));
    }

    public function debt()
    {
        /*  $clients = Client::when(Auth::user()->type == 3, function ($q) {
            $q->where('user_id', Auth::id());
        })->get(); */ // no need for this check , after switching to roles and permissions
        $clients = Client::where('user_id', Auth::id())->get();
        return view('debt', compact('clients'));
    }

    public function debt_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $client = Client::find($request->client_id) ?? null;
        $operations = Detail::with('client')
            ->when(Auth::user()->type == 3, function ($q) {
                return $q->where('user_id', Auth::id());
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('debt_result', compact('operations', 'start_date', 'end_date', 'client'));
    }

    // ── Machine Report (no time filter) ──
    public function machine_report()
    {

        $stations = Auth::user()->stations; // only show the user stations
        return view('machine_report', compact('stations'));
    }

    public function machine_report_result(Request $request)
    {
        $stationId = $request->station_id;
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        if ($stationId) $machinesQuery->where('station_id', $stationId);
        if ($machineId) $machinesQuery->where('id', $machineId);
        if ($fuelType) $machinesQuery->whereHas('stock', fn($q) => $q->where('type', $fuelType));
        $machines = $machinesQuery->get();

        $results = $machines->map(function ($machine) {
            $gunStats = \App\Models\MachineDetail::select('gun_id', DB::raw('SUM(net) as total_net'), DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as count'))
                ->where('machine_id', $machine->id)
                ->groupBy('gun_id')
                ->get()
                ->map(function ($row) {
                    $gun = \App\Models\Gun::find($row->gun_id);
                    return [
                        'gun_name' => $gun->name ?? '-',
                        'total_net' => $row->total_net,
                        'total_amount' => $row->total_amount,
                        'count' => $row->count,
                    ];
                });

            $totalNet = $gunStats->sum('total_net');
            $totalAmount = $gunStats->sum('total_amount');
            $stock = $machine->stock;

            return [
                'machine' => $machine,
                'guns' => $gunStats,
                'total_net' => $totalNet,
                'total_amount' => $totalAmount,
                'stock_name' => $stock->name ?? '-',
                'stock_type' => $stock ? ($stock->type == 1 ? 'جازولين' : 'بنزين') : '-',
                'stock_remaining' => $stock->qty ?? 0,
            ];
        });

        $station = $stationId ? Station::find($stationId) : null;
        return view('machine_report_result', compact('results', 'station'));
    }

    // ── Machine Report with Time Filter ──
    public function machine_report_time()
    {
        $stations = Auth::user()->stations;
        return view('machine_report_time', compact('stations'));
    }

    public function machine_report_time_result(Request $request)
    {
        $stationId =  $request->station_id;
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        if ($stationId) $machinesQuery->where('station_id', $stationId);
        if ($machineId) $machinesQuery->where('id', $machineId);
        if ($fuelType) $machinesQuery->whereHas('stock', fn($q) => $q->where('type', $fuelType));
        $machines = $machinesQuery->get();

        $results = $machines->map(function ($machine) use ($startDate, $endDate) {
            $detailQuery = \App\Models\MachineDetail::select('gun_id', DB::raw('SUM(net) as total_net'), DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as count'))
                ->where('machine_id', $machine->id);
            if ($startDate) $detailQuery->whereDate('date', '>=', $startDate);
            if ($endDate) $detailQuery->whereDate('date', '<=', $endDate);
            $gunStats = $detailQuery->groupBy('gun_id')->get()->map(function ($row) {
                $gun = \App\Models\Gun::find($row->gun_id);
                return [
                    'gun_name' => $gun->name ?? '-',
                    'total_net' => $row->total_net,
                    'total_amount' => $row->total_amount,
                    'count' => $row->count,
                ];
            });

            $totalNet = $gunStats->sum('total_net');
            $totalAmount = $gunStats->sum('total_amount');
            $stock = $machine->stock;

            return [
                'machine' => $machine,
                'guns' => $gunStats,
                'total_net' => $totalNet,
                'total_amount' => $totalAmount,
                'stock_name' => $stock->name ?? '-',
                'stock_type' => $stock ? ($stock->type == 1 ? 'جازولين' : 'بنزين') : '-',
                'stock_remaining' => $stock->qty ?? 0,
            ];
        });

        $station = $stationId ? Station::find($stationId) : null;
        return view('machine_report_time_result', compact('results', 'station', 'startDate', 'endDate'));
    }

    // ── General Stock Report (per stock, outside machines) ──
    public function stock_report()
    {
        $stations = Auth::user()->stations;
        return view('stock_report', compact('stations'));
    }

    public function stock_report_result(Request $request)
    {
        $stationId = Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id;
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stocksQuery = \App\Models\Stock::query()->with('station');
        if ($stationId) $stocksQuery->where('station_id', $stationId);
        if ($fuelType) $stocksQuery->where('type', $fuelType);
        $stocks = $stocksQuery->get();

        $results = $stocks->map(function ($stock) use ($startDate, $endDate) {
            // الاضافات: من التناكر (stock_details بلا تاريخ - إجمالي كلي)
            $additions = \App\Models\StockDetail::where('stock_id', $stock->id)->sum('qty');

            // المسحوبات: عبر الماكينات المرتبطة بالبير
            $withdrawQuery = MachineDetail::whereHas('machine', function ($q) use ($stock) {
                $q->where('stock_id', $stock->id);
            });
            if ($startDate) $withdrawQuery->whereDate('date', '>=', $startDate);
            if ($endDate) $withdrawQuery->whereDate('date', '<=', $endDate);

            $withdrawals = (clone $withdrawQuery)->sum('net');
            $withdrawTotal = (clone $withdrawQuery)->sum('total');

            return [
                'stock' => $stock,
                'additions' => $additions,
                'withdrawals' => $withdrawals,
                'withdraw_total' => $withdrawTotal,
                'remaining' => $stock->qty,
            ];
        });

        $station = $stationId ? Station::find($stationId) : null;
        return view('stock_report_result', compact('results', 'station', 'startDate', 'endDate'));
    }

    // ── Per-Stock Movement Report (single stock detail) ──
    public function stock_movement()
    {
        $stations = Auth::user()->stations;
        return view('stock_movement', compact('stations'));
    }

    public function stock_movement_result(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stock = \App\Models\Stock::with('station')->findOrFail($request->stock_id);

        // الاضافات: من التناكر (بلا تاريخ في stock_details)
        $additions = \App\Models\StockDetail::where('stock_id', $stock->id)
            ->get()
            ->map(function ($detail) {
                $tuncker = \App\Models\Tuncker::find($detail->tuncker_id);
                return [
                    'qty' => $detail->qty,
                    'driver_name' => $tuncker->driver_name ?? '-',
                    'tuncker_no' => $tuncker->tuncker_no ?? '-',
                    'supplier' => $tuncker->supplier->name ?? '-',
                ];
            });

        // المسحوبات: قراءات الماكينات المرتبطة بالبير
        $withdrawQuery = MachineDetail::with(['machine', 'gun'])
            ->whereHas('machine', function ($q) use ($stock) {
                $q->where('stock_id', $stock->id);
            });
        if ($startDate) $withdrawQuery->whereDate('date', '>=', $startDate);
        if ($endDate) $withdrawQuery->whereDate('date', '<=', $endDate);
        $withdrawals = $withdrawQuery->orderBy('date')->orderBy('id')->get();

        $totalAdditions = $additions->sum('qty');
        $totalWithdrawals = $withdrawals->sum('net');
        $totalWithdrawAmount = $withdrawals->sum('total');

        return view('stock_movement_result', compact(
            'stock',
            'additions',
            'withdrawals',
            'totalAdditions',
            'totalWithdrawals',
            'totalWithdrawAmount',
            'startDate',
            'endDate'
        ));
    }
}
