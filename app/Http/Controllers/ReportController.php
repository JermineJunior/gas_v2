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
        $stations = Station::get();
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
        $stations = Station::get();
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
        $operations = FuelOrder::with('supplier')
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
        $clients = Client::when(Auth::user()->type == 3, function ($q) {
            $q->where('user_id', Auth::id());
        })->get();

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
}
