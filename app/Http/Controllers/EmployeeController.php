<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Employee;
use App\Models\MachineDetail;
use App\Models\Station;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Station $station)
    {
        $employees = Employee::where('station_id', $station->id)->get();
        return view('employee', compact('employees', 'station'));
    }

    public function store(Request $request)
    {
        Employee::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
        ]);

        return back()->with('success', 'تم ادخال الموظف بنجاح');
    }

    public function update(Request $request)
    {
        Employee::find($request->id)->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'تم تعديل الموظف بنجاح');
    }

    public function delete(Employee $employee)
    {
        $employee->delete();
        return back()->with('success', ' تم حذف الموظف بنجاح');
    }

    public function get_remaining(Request $request)
    {
        $total_new_machine = MachineDetail::where('station_id', $request->station_id)->where('employee_id', $request->employee_id)->where('status', 0)->sum('total');
        $total_old_machine = Deposit::where('station_id', $request->station_id)->where('employee_id', $request->employee_id)->latest()->first()->remaining ?? 0;

        return response()->json([
            'total_new_machine' => $total_new_machine,
            'total_old_machine' => $total_old_machine,
        ]);
    }
}
