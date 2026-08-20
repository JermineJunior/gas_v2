<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withSum('fuelOrders as total_orders', 'quantity' )->withSum('fuelDeliveries as total_deliveries', 'quantity')->orderByDesc('id')->get();
        return view('supplier', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:suppliers,name',
            'phone' => 'nullable|numeric',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('supplier.index')->with('success', 'تم اضافة العميل بنجاح');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique('suppliers', 'name')->ignore($request->id, 'id')],
            'phone' => 'nullable|numeric',
        ]);

        $supplier = Supplier::findOrFail($request->id);

        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('supplier.index')->with('success', 'تم تعديل العميل بنجاح');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('supplier.index')->with('success', 'تم حذف العميل بنجاح');
    }
}
