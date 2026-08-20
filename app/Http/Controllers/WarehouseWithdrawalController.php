<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransaction;
use App\Models\WarehouseWithdrawal;
use Illuminate\Http\Request;

class WarehouseWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseWithdrawal::with('warehouse', 'transaction');

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

        $withdrawals = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $warehouses = Warehouse::all();

        return view('warehouse_withdrawal.index', compact('withdrawals', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_withdrawal.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $this->cleanNumericFields($request, ['amount']);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
            'driver_name'  => 'required|string|max:255',
            'car_number'   => 'required|string|max:255',
            'amount'       => 'required|numeric|gt:0',
            'date'         => 'required|date',
            'note'         => 'nullable|string',
        ]);

        $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
            ->where('fuel_type', $request->fuel_type)
            ->first();

        $available = $stock->current_stock ?? 0;

        if ($available <= 0) {
            return back()->withInput()->with('error', 'المستودع فارغ من هذا النوع ولا يمكن السحب');
        }

        if ($request->amount > $available) {
            return back()->withInput()->with('error', 'الكمية المطلوبة (' . formatNumber($request->amount) . ') تتجاوز الرصيد المتوفر (' . formatNumber($available) . ')');
        }

        $withdrawal = WarehouseWithdrawal::create([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'driver_name'  => $request->driver_name,
            'car_number'   => $request->car_number,
            'amount'       => $request->amount,
            'date'         => $request->date,
            'note'         => $request->note,
        ]);

        WarehouseTransaction::create([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'type'         => WarehouseTransaction::TYPE_WITHDRAWAL,
            'quantity'     => $request->amount,
            'withdrawal_id'=> $withdrawal->id,
            'date'         => $request->date,
            'note'         => $request->note,
        ]);

        Warehouse::recalculateBalance($request->warehouse_id, $request->fuel_type);

        return redirect()->route('warehouse_withdrawals.index')->with('success', 'تم اضافة السحب بنجاح');
    }

    public function edit(WarehouseWithdrawal $warehouse_withdrawal)
    {
        $warehouses = Warehouse::all();
        return view('warehouse_withdrawal.edit', compact('warehouse_withdrawal', 'warehouses'));
    }

    public function update(Request $request, WarehouseWithdrawal $warehouse_withdrawal)
    {
        $this->cleanNumericFields($request, ['amount']);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
            'driver_name'  => 'required|string|max:255',
            'car_number'   => 'required|string|max:255',
            'amount'       => 'required|numeric|gt:0',
            'date'         => 'required|date',
            'note'         => 'nullable|string',
        ]);

        $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
            ->where('fuel_type', $request->fuel_type)
            ->first();

        $currentStock = $stock->current_stock ?? 0;

        // When editing, the old withdrawal already reduced stock.
        // After deleting it, available = currentStock + oldAmount.
        $oldAmount = $warehouse_withdrawal->amount;
        $available = $currentStock + $oldAmount;

        if ($available <= 0) {
            return back()->withInput()->with('error', 'المستودع فارغ من هذا النوع ولا يمكن السحب');
        }

        if ($request->amount > $available) {
            return back()->withInput()->with('error', 'الكمية المطلوبة (' . formatNumber($request->amount) . ') تتجاوز الرصيد المتوفر (' . formatNumber($available) . ')');
        }

        $oldWarehouseId = $warehouse_withdrawal->warehouse_id;
        $oldFuelType = $warehouse_withdrawal->fuel_type;

        $transaction = $warehouse_withdrawal->transaction;

        if ($transaction) {
            $transaction->delete();
        }

        $warehouse_withdrawal->update([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'driver_name'  => $request->driver_name,
            'car_number'   => $request->car_number,
            'amount'       => $request->amount,
            'date'         => $request->date,
            'note'         => $request->note,
        ]);

        WarehouseTransaction::create([
            'warehouse_id' => $request->warehouse_id,
            'fuel_type'    => $request->fuel_type,
            'type'         => WarehouseTransaction::TYPE_WITHDRAWAL,
            'quantity'     => $request->amount,
            'withdrawal_id'=> $warehouse_withdrawal->id,
            'date'         => $request->date,
            'note'         => $request->note,
        ]);

        Warehouse::recalculateBalance($oldWarehouseId, $oldFuelType);
        if ($request->warehouse_id != $oldWarehouseId || $request->fuel_type != $oldFuelType) {
            Warehouse::recalculateBalance($request->warehouse_id, $request->fuel_type);
        }

        return redirect()->route('warehouse_withdrawals.index')->with('success', 'تم تعديل السحب بنجاح');
    }

    public function destroy(WarehouseWithdrawal $warehouse_withdrawal)
    {
        $warehouseId = $warehouse_withdrawal->warehouse_id;
        $fuelType = $warehouse_withdrawal->fuel_type;

        $warehouse_withdrawal->transaction?->delete();
        $warehouse_withdrawal->delete();

        Warehouse::recalculateBalance($warehouseId, $fuelType);

        return redirect()->route('warehouse_withdrawals.index')->with('success', 'تم حذف السحب بنجاح');
    }
}
