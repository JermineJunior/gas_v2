<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseTransaction::with('warehouse', 'relatedTransaction.warehouse')
            ->whereIn('type', [WarehouseTransaction::TYPE_TRANSFER_OUT, WarehouseTransaction::TYPE_TRANSFER_IN]);

        if ($request->filled('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id)
                    ->orWhereHas('relatedTransaction', function ($q2) use ($request) {
                        $q2->where('warehouse_id', $request->warehouse_id);
                    });
            });
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

        $transfers = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $warehouses = Warehouse::all();

        return view('warehouse_transfer.index', compact('transfers', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('warehouse_transfer.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $this->cleanNumericFields($request, ['quantity']);

        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|not_in:' . $request->from_warehouse_id,
            'fuel_type'         => 'required|in:1,2',
            'quantity'          => 'required|numeric|gt:0',
            'date'              => 'required|date',
            'note'              => 'nullable|string',
        ]);

        $stock = WarehouseStock::where('warehouse_id', $request->from_warehouse_id)
            ->where('fuel_type', $request->fuel_type)
            ->first();

        if (!$stock || $stock->current_stock < $request->quantity) {
            return back()->withInput()->with('error', 'الكمية المطلوبة غير متوفرة في المستودع المصدر');
        }

        DB::transaction(function () use ($request) {
            $outTransaction = WarehouseTransaction::create([
                'warehouse_id' => $request->from_warehouse_id,
                'fuel_type'    => $request->fuel_type,
                'type'         => WarehouseTransaction::TYPE_TRANSFER_OUT,
                'quantity'     => $request->quantity,
                'date'         => $request->date,
                'note'         => $request->note,
            ]);

            $inTransaction = WarehouseTransaction::create([
                'warehouse_id' => $request->to_warehouse_id,
                'fuel_type'    => $request->fuel_type,
                'type'         => WarehouseTransaction::TYPE_TRANSFER_IN,
                'quantity'     => $request->quantity,
                'date'         => $request->date,
                'note'         => $request->note,
            ]);

            $outTransaction->update(['related_transaction_id' => $inTransaction->id]);
            $inTransaction->update(['related_transaction_id' => $outTransaction->id]);
        });

        Warehouse::recalculateBalance($request->from_warehouse_id, $request->fuel_type);
        Warehouse::recalculateBalance($request->to_warehouse_id, $request->fuel_type);

        return redirect()->route('warehouse_transfers.index')->with('success', 'تم اجراء التحويل بنجاح');
    }

    public function destroy(WarehouseTransaction $warehouse_transfer)
    {
        $linkedTransaction = $warehouse_transfer->relatedTransaction;

        if ($linkedTransaction) {
            $warehouseId2 = $linkedTransaction->warehouse_id;
            $fuelType2 = $linkedTransaction->fuel_type;
            $linkedTransaction->delete();
        } else {
            $warehouseId2 = null;
            $fuelType2 = null;
        }

        $warehouseId1 = $warehouse_transfer->warehouse_id;
        $fuelType1 = $warehouse_transfer->fuel_type;

        $warehouse_transfer->delete();

        Warehouse::recalculateBalance($warehouseId1, $fuelType1);
        if ($warehouseId2 && $fuelType2) {
            Warehouse::recalculateBalance($warehouseId2, $fuelType2);
        }

        return redirect()->route('warehouse_transfers.index')->with('success', 'تم حذف التحويل بنجاح');
    }
}
