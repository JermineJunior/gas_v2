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
    /**
     * حساب صافي القراءة مع دعم تصفير العداد (Rollover).
     * المصدر الوحيد للحقيقة: قيم العدادات + max_counter من قاعدة البيانات.
     */
    private function parseCounter($value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    private function computeReading(float $start, float $end, float $maxCounter, string $machineName): array
    {
        if ($start < 0 || $end < 0) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                back()->withErrors("الماكينة \"{$machineName}\": قيم العداد لا يمكن أن تكون سالبة")
            );
        }

        if ($start > $maxCounter || $end > $maxCounter) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                back()->withErrors(
                    "الماكينة \"{$machineName}\": قيمة العداد تتجاوز الحد الأقصى للعداد (" . number_format($maxCounter) . ')'
                )
            );
        }

        // end >= start: قراءة عادية | end < start: حدث تصفير للعداد
        $isRollover = $end < $start;
        $net = $isRollover ? (($maxCounter - $start) + $end) : ($end - $start);

        return [$net, $isRollover];
    }

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

        $data = [];

        foreach ($request->machine_id as $index => $machineId) {
            $machine = Machine::find($machineId);
            if (!$machine) {
                return back()->withErrors('الماكينة غير موجودة في الصف رقم ' . ($index + 1));
            }

            $start = $this->parseCounter($request->start_counter[$index]);
            $end = $this->parseCounter($request->end_counter[$index]);
            $price = (float) str_replace(',', '', $request->price[$index]);

            try {
                [$net, $isRollover] = $this->computeReading($start, $end, (float) $machine->max_counter, $machine->name);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }

            // فحص رصيد البير مقابل الصافي المحسوب من السيرفر
            if ($machine->stock_id) {
                $stock = Stock::find($machine->stock_id);
                if ($stock && $net > $stock->qty) {
                    return back()->withErrors(
                        'الكمية المطلوبة (' . number_format($net) . ' لتر) للماكينة "' . $machine->name . '" تتجاوز رصيد البير "' . $stock->name . '" المتوفر (' . number_format($stock->qty) . ' لتر)'
                    )->withInput();
                }
            }

            $data[] = [
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'station_id' => $request->station_id,
                'machine_id' => $machine->id,
                'gun_id' => $request->gun_id[$index],
                'start_counter' => $start,
                'end_counter' => $end,
                'net' => $net,
                'is_rollover' => $isRollover,
                'price' => $price,
                'total' => $net * $price,
            ];

            if ($machine->stock_id) {
                $stock = Stock::find($machine->stock_id);
                if ($stock) {
                    $stock->update(['qty' => $stock->qty - $net]);
                }
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
            $machine = Machine::find($machine_id);
            if (!$machine) {
                return back()->withErrors('الماكينة غير موجودة في الصف رقم ' . ($index + 1));
            }

            $start = $this->parseCounter($request->start_counter[$index]);
            $end = $this->parseCounter($request->end_counter[$index]);
            $price = (float) str_replace(',', '', $request->price[$index]);

            try {
                [$net, $isRollover] = $this->computeReading($start, $end, (float) $machine->max_counter, $machine->name);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }

            $data[] = [
                'date' => today(),
                'employee_id' => $request->employee_id,
                'station_id' => $request->station_id,
                'machine_id' => $machine->id,
                'gun_id' => $request->gun_id[$index],
                'start_counter' => $start,
                'end_counter' => $end,
                'net' => $net,
                'is_rollover' => $isRollover,
                'price' => $price,
                'total' => $net * $price,
            ];

            $stock = Stock::find($machine->stock_id);

            if ($stock) {
                $stock->update([
                    'qty' => $stock->qty - $net + $qty,
                ]);
            }
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
