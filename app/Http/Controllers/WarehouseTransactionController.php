<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class WarehouseTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseTransaction::with('warehouse')
            ->where('type', WarehouseTransaction::TYPE_ADDITION);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $warehouses = Warehouse::all();

        return view('warehouse_transaction.index', compact('transactions', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_transaction.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $this->cleanNumericFields($request, ['quantity']);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
            'driver_name'  => 'required|string|max:255',
            'car_number'   => 'required|string|max:255',
            'quantity'     => 'required|numeric|gt:0',
            'date'         => 'required|date',
            'source'       => 'nullable|string|max:255',
            'note'         => 'nullable|string',
        ]);

        WarehouseTransaction::create([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'type'         => WarehouseTransaction::TYPE_ADDITION,
            'quantity'     => $request->quantity,
            'driver_name'  => $request->driver_name,
            'car_number'   => $request->car_number,
            'date'         => $request->date,
            'source'       => $request->source,
            'note'         => $request->note,
        ]);

        Warehouse::recalculateBalance($request->warehouse_id, $request->fuel_type);

        return redirect()->route('warehouse_transactions.index')->with('success', 'تم اضافة الحركة بنجاح');
    }

    public function edit(WarehouseTransaction $warehouse_transaction)
    {
        $warehouses = Warehouse::all();
        return view('warehouse_transaction.edit', compact('warehouse_transaction', 'warehouses'));
    }

    public function update(Request $request, WarehouseTransaction $warehouse_transaction)
    {
        $this->cleanNumericFields($request, ['quantity']);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
            'driver_name'  => 'required|string|max:255',
            'car_number'   => 'required|string|max:255',
            'quantity'     => 'required|numeric|gt:0',
            'date'         => 'required|date',
            'source'       => 'nullable|string|max:255',
            'note'         => 'nullable|string',
        ]);

        $oldWarehouseId = $warehouse_transaction->warehouse_id;
        $oldFuelType = $warehouse_transaction->fuel_type;

        $warehouse_transaction->update([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'quantity'     => $request->quantity,
            'driver_name'  => $request->driver_name,
            'car_number'   => $request->car_number,
            'date'         => $request->date,
            'source'       => $request->source,
            'note'         => $request->note,
        ]);

        Warehouse::recalculateBalance($oldWarehouseId, $oldFuelType);
        if ($request->warehouse_id != $oldWarehouseId || $request->fuel_type != $oldFuelType) {
            Warehouse::recalculateBalance($request->warehouse_id, $request->fuel_type);
        }

        return redirect()->route('warehouse_transactions.index')->with('success', 'تم تعديل الحركة بنجاح');
    }

    public function destroy(WarehouseTransaction $warehouse_transaction)
    {
        $warehouseId = $warehouse_transaction->warehouse_id;
        $fuelType = $warehouse_transaction->fuel_type;

        $warehouse_transaction->delete();

        Warehouse::recalculateBalance($warehouseId, $fuelType);

        return redirect()->route('warehouse_transactions.index')->with('success', 'تم حذف الحركة بنجاح');
    }
}
