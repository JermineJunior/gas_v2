<?php

namespace App\Http\Controllers;

use App\Models\Gun;
use App\Models\Machine;
use Illuminate\Http\Request;

class GunContorller extends Controller
{
    public function get_machine(Request $request)
    {
        $machines = Machine::where('station_id',$request->station_id)->get();
        return response(['success'=> true,'machines' => $machines]);
    }

    public function get_gun(Request $request)
    {
        $guns = Gun::where('station_id',$request->station_id)->where('machine_id',$request->machine_id)->get();
        return response(['success'=> true,'guns' => $guns]);
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'station_id' => 'required|exists:stations,id',
            'machine_id' => 'required|exists:machines,id',
        ]);
        $exists = Gun::where(['name' => $request->name, 'station_id' => $request->station_id,'machine_id' => $request->machine_id])->exists();
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكنك تسجيل المسدس لانه مستخدم من قبل في النظام',
            ]);
        }
        $gun = Gun::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
            'machine_id' => $request->machine_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة المسدس بنجاح ✅',
            'gun' => $gun,
        ]);
    }


}
