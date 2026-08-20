<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('stocks')->get();
        return view('warehouse.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouse.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Warehouse::create(['name' => $request->name]);
        return redirect()->route('warehouses.index')->with('success', 'تم اضافة المستودع بنجاح');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $warehouse->update(['name' => $request->name]);
        return redirect()->route('warehouses.index')->with('success', 'تم تعديل المستودع بنجاح');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->transactions()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المستودع لوجود حركات مرتبطة به');
        }

        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'تم حذف المستودع بنجاح');
    }
}
