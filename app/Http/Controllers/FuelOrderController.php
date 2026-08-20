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
        $quantity = str_replace(',','',$request->quantity);
        FuelOrder::create([
            'supplier_id' => $request->supplier_id,
            'date' => $request->date,
            'quantity' => $quantity,
            'user_id' => Auth::id()
        ]);
         
        return back()->with('success','تم ادخال كمية الوقود بنجاح');
    }

    public function update(Request $request)
    {
        $fuel_order = FuelOrder::findOrFail($request->id);
        $quantity = str_replace(',','',$request->quantity);
        $fuel_order->update([
            'date' => $request->date,
            'quantity' => $quantity,
        ]);
        
        return back()->with('success','تم تعديل البيانات بنجاح');
    }

    public function destroy(FuelOrder $fuel_order)
    {
        $fuel_order->delete();
        return back()->with('success','تم حذف البيانات بنجاح');
    }
}
