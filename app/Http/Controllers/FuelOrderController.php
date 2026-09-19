<?php

namespace App\Http\Controllers;

use App\Models\FuelOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelOrderController extends Controller
{
    public function index(Supplier $supplier)
    {
        $fuel_orders = FuelOrder::where('supplier_id',$supplier->id)->orderByDesc('id')->get();
        return view('fuel_order',compact('fuel_orders','supplier'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'quantity' => str_replace(',', '', $request->quantity),
            'price_per_liter' => str_replace(',', '', $request->price_per_liter),
            'total' => str_replace(',', '', $request->total),
        ]);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:1,2',
            'price_per_liter' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        FuelOrder::create([
            'supplier_id' => $request->supplier_id,
            'date' => $request->date,
            'quantity' => $request->quantity,
            'fuel_type' => $request->fuel_type,
            'price_per_liter' => $request->price_per_liter,
            'total' => $request->total, // editable field — saved as-is, not recomputed server-side
            'user_id' => Auth::id()
        ]);

        return back()->with('success', 'تم ادخال كمية الوقود بنجاح');
    }

    public function update(Request $request)
    {
        $request->merge([
            'quantity' => str_replace(',', '', $request->quantity),
            'price_per_liter' => str_replace(',', '', $request->price_per_liter),
            'total' => str_replace(',', '', $request->total),
        ]);

        $request->validate([
            'id' => 'required|exists:fuel_orders,id',
            'date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:1,2',
            'price_per_liter' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        $fuel_order = FuelOrder::findOrFail($request->id);
        $fuel_order->update([
            'date' => $request->date,
            'quantity' => $request->quantity,
            'fuel_type' => $request->fuel_type,
            'price_per_liter' => $request->price_per_liter,
            'total' => $request->total, // editable field — saved as-is, not recomputed server-side
        ]);

        return back()->with('success', 'تم تعديل البيانات بنجاح');
    }

    public function destroy(FuelOrder $fuel_order)
    {
        $fuel_order->delete();
        return back()->with('success','تم حذف البيانات بنجاح');
    }
}
