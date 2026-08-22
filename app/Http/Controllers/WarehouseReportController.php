<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseReportController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_report.index', compact('warehouses'));
    }

    public function result(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = $warehouse->transactions()
            ->where('fuel_type', $fuelType)
            ->with(['withdrawal', 'relatedTransaction.warehouse']);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $transactions = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $currentBalance = $transactions->last()?->balance_after ?? 0;

        return view('warehouse_report.result', compact('warehouse', 'fuelType', 'startDate', 'endDate', 'transactions', 'currentBalance'));
    }

    // ── Withdrawals Report ──
    public function withdrawals()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_report.withdrawals', compact('warehouses'));
    }

    public function withdrawalsResult(Request $request)
    {
        $query = \App\Models\WarehouseTransaction::with(['warehouse', 'withdrawal'])
            ->where('type', \App\Models\WarehouseTransaction::TYPE_WITHDRAWAL)
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->fuel_type, fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->when($request->driver_name, fn($q) => $q->where('driver_name', 'like', '%' . $request->driver_name . '%'))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date));

        $transactions = $query->orderBy('date')->orderBy('id')->get();
        $total = $transactions->sum('quantity');

        return view('warehouse_report.withdrawals_result', compact('transactions', 'total'));
    }

    // ── Additions Report ──
    public function additions()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_report.additions', compact('warehouses'));
    }

    public function additionsResult(Request $request)
    {
        $query = \App\Models\WarehouseTransaction::with('warehouse')
            ->where('type', \App\Models\WarehouseTransaction::TYPE_ADDITION)
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->fuel_type, fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->when($request->source, fn($q) => $q->where('source', 'like', '%' . $request->source . '%'))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date));

        $transactions = $query->orderBy('date')->orderBy('id')->get();
        $total = $transactions->sum('quantity');

        return view('warehouse_report.additions_result', compact('transactions', 'total'));
    }

    // ── Transfers Report (between warehouses) ──
    public function transfers()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_report.transfers', compact('warehouses'));
    }

    public function transfersResult(Request $request)
    {
        // كل تحويل ينتج صفين (صادر + وارد) مرتبطين؛ نعرض صف الصادر كممثل للتحويل
        $query = \App\Models\WarehouseTransaction::with(['warehouse', 'relatedTransaction.warehouse'])
            ->where('type', \App\Models\WarehouseTransaction::TYPE_TRANSFER_OUT)
            ->when($request->from_warehouse_id, fn($q) => $q->where('warehouse_id', $request->from_warehouse_id))
            ->when($request->to_warehouse_id, fn($q) => $q->whereHas('relatedTransaction', fn($r) => $r->where('warehouse_id', $request->to_warehouse_id)))
            ->when($request->fuel_type, fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date));

        $transfers = $query->orderBy('date')->orderBy('id')->get();
        $total = $transfers->sum('quantity');

        return view('warehouse_report.transfers_result', compact('transfers', 'total'));
    }

    // ── Current Stock Summary ──
    public function summary()
    {
        $warehouses = Warehouse::with('stocks')->orderBy('name')->get();
        $grandGasoline = $warehouses->sum(fn($w) => $w->stocks->where('fuel_type', 1)->sum('current_stock'));
        $grandBenzine = $warehouses->sum(fn($w) => $w->stocks->where('fuel_type', 2)->sum('current_stock'));

        return view('warehouse_report.summary', compact('warehouses', 'grandGasoline', 'grandBenzine'));
    }

    // ── Consumption Report ──
    public function consumption()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_report.consumption', compact('warehouses'));
    }

    public function consumptionResult(Request $request)
    {
        $query = \App\Models\WarehouseTransaction::query()
            ->where('type', \App\Models\WarehouseTransaction::TYPE_WITHDRAWAL)
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date));

        $byFuelType = (clone $query)->selectRaw('fuel_type, SUM(quantity) as total')
            ->groupBy('fuel_type')
            ->pluck('total', 'fuel_type');

        $byWarehouse = (clone $query)->selectRaw('warehouse_id, fuel_type, SUM(quantity) as total')
            ->groupBy('warehouse_id', 'fuel_type')
            ->get()
            ->groupBy('warehouse_id');

        $warehouses = Warehouse::whereIn('id', $byWarehouse->keys())->get()->keyBy('id');
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $grandTotal = $byFuelType->sum();

        return view('warehouse_report.consumption_result', compact(
            'byFuelType', 'byWarehouse', 'warehouses', 'startDate', 'endDate', 'grandTotal'
        ));
    }
}
