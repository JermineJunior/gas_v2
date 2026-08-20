<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function index(Station $station)
    {
        $stocks = Stock::where('station_id', $station->id)->get();
        return view('stock', compact('stocks', 'station'));
    }

    public function store(Request $request)
    {
        $exists = Stock::where('station_id', $request->station_id)->where('name', $request->name)->exists();
        if ($exists) {
            return back()->withErrors('قيمة الاسم مستخدمة من قبل في النظام');
        }
        Stock::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
            'type' => $request->type,
        ]);

        return back()->with('success', 'تم ادخال البير بنجاح');
    }

    public function update(Request $request)
    {
        $stock = Stock::find($request->id);
        $exists = Stock::where('station_id', $stock->station_id)->where('name', $request->name)->whereNot('id', $stock->id)->exists();
        if ($exists) {
            return back()->withErrors('قيمة الاسم مستخدمة من قبل في النظام');
        }
        $stock->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return back()->with('success', 'تم تعديل البير بنجاح');
    }

    public function delete(Stock $stock)
    {
        $stock->delete();
        return back()->with('success', 'تم حذف البير بنجاح');
    }

    public function getByType(Request $request)
    {
        $stocks = Stock::where('station_id', $request->station_id)
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->get();

        return response()->json($stocks);
    }
}
