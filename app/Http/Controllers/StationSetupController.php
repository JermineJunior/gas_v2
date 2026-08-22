<?php

namespace App\Http\Controllers;

use App\Models\Gun;
use App\Models\Stock;
use App\Models\Machine;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationSetupController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }

        $stocks = Stock::where('station_id', $station->id)
            ->with('machines.guns')
            ->orderBy('id')
            ->get();

        return view('station_setup', compact('station', 'stocks'));
    }

    public function storeStock(Request $request, Station $station)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:stocks,name,NULL,id,station_id,' . $station->id,
            'type' => 'required|in:1,2',
            'qty'  => 'nullable|numeric|min:0',
        ]);

        Stock::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'qty'        => $request->qty ?? 0,
            'station_id' => $station->id,
        ]);

        return back()->with('success', 'تمت إضافة البير بنجاح');
    }

    public function storeMachine(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'stock_id'    => 'required|exists:stocks,id',
            'station_id'  => 'required|exists:stations,id',
            'max_counter' => 'nullable|numeric|min:1',
        ]);

        Machine::create([
            'name'        => $request->name,
            'stock_id'    => $request->stock_id,
            'station_id'  => $request->station_id,
            'max_counter' => $request->max_counter ?: 9999999,
        ]);

        return back()->with('success', 'تمت إضافة الماكينة بنجاح');
    }

    public function storeGun(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'machine_id' => 'required|exists:machines,id',
            'station_id' => 'required|exists:stations,id',
        ]);

        if (Gun::where('machine_id', $request->machine_id)->count() >= 2) {
            return back()->withErrors(['name' => 'لا يمكن إضافة أكثر من مسدسين للماكينة الواحدة']);
        }

        $exists = Gun::where([
            'name'       => $request->name,
            'station_id' => $request->station_id,
            'machine_id' => $request->machine_id,
        ])->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'لا يمكنك تسجيل المسدس لانه مستخدم من قبل في النظام']);
        }

        Gun::create([
            'name'       => $request->name,
            'machine_id' => $request->machine_id,
            'station_id' => $request->station_id,
        ]);

        return back()->with('success', 'تمت إضافة المسدس بنجاح');
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:stocks,id',
            'name' => 'required|string|max:255|unique:stocks,name,' . $request->id . ',id,station_id,' . $request->station_id,
            'type' => 'required|in:1,2',
            'qty'  => 'nullable|numeric|min:0',
        ]);

        $stock = Stock::findOrFail($request->id);
        $stock->update([
            'name' => $request->name,
            'type' => $request->type,
            'qty'  => $request->qty ?? 0,
        ]);

        return back()->with('success', 'تم تعديل البير بنجاح');
    }

    public function updateMachine(Request $request)
    {
        $request->validate([
            'id'          => 'required|exists:machines,id',
            'name'        => 'required|string|max:255',
            'max_counter' => 'nullable|numeric|min:1',
        ]);

        $machine = Machine::findOrFail($request->id);
        $machine->update([
            'name'        => $request->name,
            'max_counter' => $request->max_counter ?: ($machine->max_counter ?: 9999999),
        ]);

        return back()->with('success', 'تم تعديل الماكينة بنجاح');
    }

    public function updateGun(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:guns,id',
            'name' => 'required|string|max:255',
        ]);

        $gun = Gun::findOrFail($request->id);

        $duplicate = Gun::where('machine_id', $gun->machine_id)
            ->where('id', '!=', $gun->id)
            ->where('name', $request->name)
            ->exists();
        if ($duplicate) {
            return back()->withErrors(['name' => 'لا يمكن تكرار اسم المسدس لنفس الماكينة']);
        }

        $gun->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'تم تعديل المسدس بنجاح');
    }

    public function destroyStock(Stock $stock)
    {
        if ($stock->machines()->exists()) {
            return back()->with('error', 'لا يمكن حذف البير لانه يحتوي على ماكينات، احذف الماكينات أولاً');
        }

        $stock->delete();
        return back()->with('success', 'تم حذف البير بنجاح');
    }

    public function destroyMachine(Machine $machine)
    {
        if ($machine->guns()->exists()) {
            return back()->with('error', 'لا يمكن حذف الماكينة لانها تحتوي على مسدسات، احذف المسدسات أولاً');
        }

        $machine->delete();
        return back()->with('success', 'تم حذف الماكينة بنجاح');
    }

    public function destroyGun(Gun $gun)
    {
        $gun->delete();
        return back()->with('success', 'تم حذف المسدس بنجاح');
    }
}
