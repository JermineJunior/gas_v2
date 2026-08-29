<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Employee;
use App\Models\ExpenseDetail;
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
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);

        Employee::create([
            'name' => $request->name,
            'station_id' => $request->station_id,
            'phone_number' => $request->phone_number,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return back()->with('success', 'تم ادخال الموظف بنجاح');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);

        Employee::find($request->id)->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return back()->with('success', 'تم تعديل الموظف بنجاح');
    }

    public function delete(Employee $employee)
    {
        $employee->delete();
        return back()->with('success', ' تم حذف الموظف بنجاح');
    }

    /**
     * مكونات التصفية لموظف في محطة — نموذج الرصيد المرحَّل:
     * - total_new_machine: مجموع القراءات غير المسوّاة (status=0) لهذا الموظف في هذه المحطة
     *   (ليست مقيدة بتاريخ — تشمل كل الأيام المتراكمة غير المسوّاة)
     * - expenses_total: مجموع بنود المصروفات التي يطابق تاريخها إحدى تواريخ تلك القراءات
     *   (المصروفات بلا employee_id ولا حالة تسوية، فالتطبيق بتقاطع التواريخ فقط)
     * - total_old_machine: متبقي آخر توريد سابق لنفس الموظف/المحطة (0 إن لا يوجد)
     */
    public function get_remaining(Request $request)
    {
        $unsettledMachines = MachineDetail::where('station_id', $request->station_id)
            ->where('employee_id', $request->employee_id)
            ->where('status', 0)
            ->get();

        $total_new_machine = $unsettledMachines->sum('total');
        $unsettledDates = $unsettledMachines->pluck('date')->unique();

        $expenses_total = ExpenseDetail::whereHas('expense', function ($q) use ($request, $unsettledDates) {
                $q->where('station_id', $request->station_id)
                  ->whereIn('date', $unsettledDates);
            })->sum('expense_amount');

        $total_old_machine = Deposit::where('station_id', $request->station_id)->where('employee_id', $request->employee_id)->latest()->first()->remaining ?? 0;

        return response()->json([
            'total_new_machine' => $total_new_machine,
            'total_old_machine' => $total_old_machine,
            'expenses_total' => $expenses_total,
        ]);
    }
}
