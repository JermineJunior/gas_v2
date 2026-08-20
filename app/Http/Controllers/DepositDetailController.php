<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Employee;
use App\Models\MachineDetail;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositDetailController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }
        $deposits = Deposit::with('deposit_details')
            ->where('station_id', $station->id)
            ->orderBy('date', 'desc') // أولاً نرتب بالتاريخ من الأحدث للأقدم
            ->get()
            ->groupBy(function (Deposit $item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d'); // تجميع بالسنة والشهر
            })
            ->sortKeysDesc() // نخلي آخر شهر يظهر أول
            ->map(function ($group) {
                // نرتب بيانات كل شهر حسب رقم التنكر تصاعديًا
                return $group->sortBy('tuncker_no');
            });

        return view('list_deposit', compact('deposits', 'station'));
    }

    public function create(Station $station)
    {
        $employees = Employee::where('station_id', $station->id)->get();
        return view('deposit_detail_create', compact('employees', 'station'));
    }

    public function store(Request $request)
    {
        $data = [];

        $deposit = Deposit::create([
            'station_id' => $request->station_id,
            'date' => $request->date,
            'employee_id' => $request->employee_id,
            'total_new_machine' => $request->total_new_machine,
            'total_old_machine' => $request->total_old_machine,
            'remaining' => $request->remaining,
        ]);

        foreach ($request->deposit_amount as $index => $amount) {
            $data[] = [
                'date' => $request->date,
                'deposit_id' => $deposit->id,
                'station_id' => $request->station_id,
                'deposit_amount' => $amount,
                'deposit_desc' => $request->deposit_desc[$index],
            ];
        }

        DepositDetail::insert($data);

        return back()->with('success', 'تم ادخال التوريدات بنجاح');
    }

    public function edit(Deposit $deposit)
    {
        $deposit = $deposit->load(['station']);
        $employees = Employee::get();
        return view('deposit_detail_edit', compact('deposit', 'employees'));
    }

    public function update(Deposit $deposit, Request $request)
    {
        $data = [];
        $deposit->delete();

        $deposit = Deposit::create([
            'station_id' => $request->station_id,
            'date' => $request->date,
            'employee_id' => $request->employee_id,
            'total_new_machine' => $request->total_new_machine,
            'total_old_machine' => $request->total_old_machine,
            'remaining' => $request->remaining,
        ]);

        foreach ($request->deposit_amount as $index => $amount) {
            $data[] = [
                'date' => $request->date,
                'deposit_id' => $deposit->id,
                'station_id' => $request->station_id,
                'deposit_amount' => $amount,
                'deposit_desc' => $request->deposit_desc[$index],
            ];
        }

        DepositDetail::insert($data);

        return redirect()->route('deposit_detail.index', $deposit->station_id)->with('success', 'تم تحديث  التوريدات بنجاح');
    }

    public function destroy(DepositDetail $deposit_detail)
    {
        $deposit_detail->delete();
        $exists = DepositDetail::where('deposit_id',$deposit_detail->deposit_id)->exists();
        if (!$exists) {
            Deposit::where('id',$deposit_detail->deposit_id)->delete();
        }

        return back()->with('success', 'تم حذف التوريد بنجاح');
    }
}
