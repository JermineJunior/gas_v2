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
}
