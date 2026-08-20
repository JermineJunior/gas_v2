<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\Operation;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::orderBy('order')->get();
        return view('index', compact('stations'));
    }

    public function store(Request $request)
    {
        // التحقق من المدخلات
        $validated = $request->validate([
            'name' => 'required|unique:stations,name',
        ]);

        $orderNum = Station::max('order') + 1;
        // إنشاء محطة جديدة
        $station = new Station();
        $station->name = $validated['name'];
        $station->order = $orderNum;
        $station->save();

        // إعادة توجيه مع رسالة نجاح
        return redirect()->route('station.index')->with('success', 'تمت إضافة الطرمبة بنجاح ✅');
    }

    public function update(Request $request, Station $station)
    {
        $request->validate([
            'name' => ['required', Rule::unique('stations', 'name')->ignore($station->id)],
        ]);

        $station->update([
            'name' => $request->name,
        ]);

        return redirect()->route('station.index')->with('success', 'تم تعديل الطرمبة بنجاح ✅');
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return redirect()->route('station.index')->with('success', 'تم حذف الطرمبة بنجاح ✅');
    }
}
