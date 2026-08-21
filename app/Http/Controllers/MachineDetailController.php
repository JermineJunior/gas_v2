<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Gun;
use App\Models\Machine;
use App\Models\MachineDetail;
use App\Models\Station;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineDetailController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }
        $machines = MachineDetail::where('station_id', $station->id)
            ->orderBy('date', 'desc') // أولاً نرتب بالتاريخ من الأحدث للأقدم
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d'); // تجميع بالسنة والشهر
            })
            ->sortKeysDesc() // نخلي آخر شهر يظهر أول
            ->map(function ($group) {
                // نرتب بيانات كل شهر حسب رقم التنكر تصاعديًا
                return $group->sortBy('tuncker_no');
            });

        return view('list_machine', compact('machines', 'station'));
    }

    public function create(Station $station)
    {
        $machines = Machine::get();
        $employees = Employee::where('station_id', $station->id)->get();
        $stocks = Stock::where('station_id', $station->id)->get();
        return view('machine_detail_create', compact('machines', 'station', 'employees', 'stocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'date' => 'required|date',
            'station_id' => 'required',
        ]);

        // Check stock for each machine before saving
        foreach ($request->machine_id as $index => $machineId) {
            $machine = Machine::find($machineId);
            if (!$machine || !$machine->stock_id) {
                continue;
            }
            $stock = Stock::find($machine->stock_id);
            if (!$stock) {
                continue;
            }
            $net = str_replace(',', '', $request->net[$index]);
            if ($net > $stock->qty) {
                return back()->withErrors(
                    'الكمية المطلوبة (' . number_format($net) . ' لتر) تتجاوز رصيد البير "' . $stock->name . '" المتوفر (' . number_format($stock->qty) . ' لتر)'
                );
            }
        }

        $data = [];

        foreach ($request->machine_id as $index => $machine) {
            $data[] = [
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'station_id' => $request->station_id,
                'machine_id' => $machine,
                'gun_id' => $request->gun_id[$index],
                'start_counter' => $request->start_counter[$index],
                'end_counter' => $request->end_counter[$index],
                'net' => str_replace(',', '', $request->net[$index]),
                'price' => $request->price[$index],
                'total' => $request->total[$index],
            ];

            $machine = Machine::find($machine);

            $stock = Stock::find($machine->stock_id);

            if ($stock) {
                $stock->update([
                    'qty' => $stock->qty - str_replace(',', '', $request->net[$index]),
                ]);
            }
        }

        MachineDetail::insert($data);

        return back()->with('success', 'تم ادخال العدادات بنجاح');
    }

    public function edit(MachineDetail $machine_detail)
    {
        $machine_detail = $machine_detail->load('station');
        $machines = Machine::get();
        $employees = Employee::where('station_id', $machine_detail->station_id)->get();
        $guns = Gun::where('station_id', $machine_detail->station_id)->where('machine_id', $machine_detail->machine_id)->get();
        return view('machine_detail_edit', compact('machine_detail', 'machines', 'employees', 'guns'));
    }

    public function update(MachineDetail $machine_detail, Request $request)
    {
        $data = [];
        $qty = $machine_detail->net;
        $station_id = $machine_detail->station_id;
        $machine_detail->delete();

        foreach ($request->machine_id as $index => $machine_id) {
            $data[] = [
                'date' => today(),
                'employee_id' => $request->employee_id,
                'station_id' => $request->station_id,
                'machine_id' => $machine_id,
                'gun_id' => $request->gun_id[$index],
                'start_counter' => $request->start_counter[$index],
                'end_counter' => $request->end_counter[$index],
                'net' => $request->net[$index],
                'price' => $request->price[$index],
                'total' => $request->total[$index],
            ];

            $machine = Machine::find($machine_id);

            $stock = Stock::find($machine->stock_id);

            $stock->update([
                'qty' => $stock->qty - $request->net[$index] + $qty,
            ]);
        }

        MachineDetail::insert($data);

        return redirect()->route('machine_detail.index', $station_id)->with('success', 'تم تحديث  العدادات بنجاح');
    }

    public function destroy(MachineDetail $machine_detail)
    {
        $machine = Machine::find($machine_detail->machine_id);

        $stock = Stock::find($machine->stock_id);

        $stock->update([
            'qty' => $stock->qty + $machine_detail->net,
        ]);
        $machine_detail->delete();
        return back()->with('success', 'تم حذف العداد بنجاح');
    }
}
