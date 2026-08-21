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
        $request->validate([
            'name' => 'required|string|max:255|unique:stocks,name,NULL,id,station_id,' . $request->station_id,
            'station_id' => 'required|exists:stations,id',
            'type' => 'required|in:1,2',
        ]);

        Stock::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
            'type' => $request->type,
        ]);

        return back()->with('success', 'تم ادخال البير بنجاح');
    }

    public function update(Request $request)
    {
        $stock = Stock::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255|unique:stocks,name,' . $stock->id . ',id,station_id,' . $stock->station_id,
            'type' => 'required|in:1,2',
        ]);

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
