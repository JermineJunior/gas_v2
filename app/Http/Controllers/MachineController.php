<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'station_id' => 'required|exists:stations,id',
            'stock_id' => 'required|exists:stocks,id',
        ]);
        $exists = Machine::where(['name' => $request->name, 'station_id' => $request->station_id])->exists();
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكنك تسجيل الماكنية لانها مستخدمة من قبل في النظام',
            ]);
        }
        $machine = Machine::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
            'stock_id' => $request->stock_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة الماكينة بنجاح ✅',
            'machine' => $machine,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'station_id' => 'required|exists:stations,id',
        ]);

        $machine = Machine::findOrFail($request->id);

        $exists = Machine::where('name', $request->name)->where('station_id', $request->station_id)->where('id', '!=', $machine->id)->exists();

        if ($exists) {
            return back()->with('error', 'لا يمكنك تسجيل الماكينة لأنها مستخدمة من قبل في النظام');
        }

        $machine->update([
            'name' => $request->name,
            'station_id' => $request->station_id,
        ]);

        return back()->with('success', 'تم تعديل البيانات بنجاح');
    }

    public function delete(Machine $machine)
    {
        $machine->delete();
        return back()->with('success', 'تم حذف البيانات بنجاح');
    }

}
