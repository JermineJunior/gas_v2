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

    private function cleanFk($value): ?int
    {
        $v = trim((string) ($value ?? ''));
        return $v !== '' && $v !== '0' ? (int) $v : null;
    }

    private function computeReading(float $start, float $end, float $maxCounter, string $machineName, bool $useRollover = true, ?float $allowedRollover = null): array
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

        // end >= start: قراءة عادية
        if ($end >= $start) {
            return [$end - $start, false, false];
        }

        // end < start: تصفير العداد — مسموح فقط إذا كانت الماكينة تدعمه
        if (!$useRollover) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                back()->withErrors("الماكينة \"{$machineName}\": عداد النهاية أقل من عداد البداية ولا يدعم هذا الماكينة حساب تصفير العداد")
            );
        }

        // تصفير مقبول تلقائياً أو يحتاج موافقة حسب الحد المسموح
        $rolloverNet = ($maxCounter - $start) + $end;
        $requiresApproval = ($allowedRollover !== null && $rolloverNet > $allowedRollover);

        return [$rolloverNet, true, $requiresApproval];
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

        // ملخص مطابقة الوردية لكل يوم (عرض فقط)
        $summaries = $machines->mapWithKeys(function ($group, $date) use ($station) {
            return [$date => MachineDetail::shiftSummary($station->id, $date)];
        });

        return view('list_machine', compact('machines', 'station', 'summaries'));
    }

    public function create(Station $station)
    {
        // الماكينات مع البير (لنوع الوقود) ومرتبة طبيعياً حسب الاسم (مكنة 2 قبل مكنة 10)
        $machines = Machine::where('station_id', $station->id)
            ->with('stock')
            ->get()
            ->sortBy(fn($m) => (string) $m->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            // نُخرج العربية فقط أساساً البير محمَّل، مع إضافة fuel_type للاستخدام في الواجهة
            ->map(function ($m) {
                return $m->load('stock');
            });

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

        // المرحلة 1: حساب جميع القراءات والتحقق من صحتها (بدون تعديل أي شيء)
        $rows = [];
        foreach ($request->machine_id as $index => $machineId) {
            if (!$machineId) continue;

            $machine = Machine::find($machineId);
            if (!$machine) {
                return back()->withErrors('الماكينة غير موجودة في الصف رقم ' . ($index + 1));
            }

            $start = $this->parseCounter($request->start_counter[$index] ?? '0');
            $end = $this->parseCounter($request->end_counter[$index] ?? '0');
            $price = (float) str_replace(',', '', $request->price[$index] ?? '0');

            if ($start == 0 && $end == 0 && $price == 0) continue;

            try {
                [$net, $isRollover, $requiresApproval] = $this->computeReading(
                    $start,
                    $end,
                    (float) $machine->max_counter,
                    $machine->name,
                    (bool) ($machine->use_rollover ?? true),
                    $machine->allowed_rollover !== null ? (float) $machine->allowed_rollover : null
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                return $e->getResponse()->withInput();
            }

            $rows[] = [
                'index'              => $index,
                'machine'            => $machine,
                'start'              => $start,
                'end'                => $end,
                'price'              => $price,
                'net'                => $net,
                'isRollover'         => $isRollover,
                'requiresApproval'   => $requiresApproval,
            ];
        }

        if (empty($rows)) {
            return back()->withErrors('لم يتم إدخال أي بيانات');
        }

        // المرحلة 2: فحص الرصيد لكل بير بشكل إجمالي قبل أي خصم
        $stockDeductions = []; // stock_id => total deduction
        foreach ($rows as $row) {
            if ($row['requiresApproval']) continue;
            $machine = $row['machine'];
            if (!$machine->stock_id) continue;
            if (!isset($stockDeductions[$machine->stock_id])) {
                $stockDeductions[$machine->stock_id] = 0;
            }
            $stockDeductions[$machine->stock_id] += $row['net'];
        }

        foreach ($stockDeductions as $stockId => $totalDeduction) {
            $stock = Stock::find($stockId);
            if ($stock && $totalDeduction > $stock->qty) {
                return back()->withErrors(
                    'الكمية المطلوبة من بير "' . $stock->name . '" (' . number_format($totalDeduction) . ' لتر) تتجاوز الرصيد المتوفر (' . number_format($stock->qty) . ' لتر)'
                )->withInput();
            }
        }

        // المرحلة 3: حفظ كل شيء في transaction واحد
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $request) {
                $data = [];
                foreach ($rows as $row) {
                    $machine = $row['machine'];
                    $data[] = [
                        'employee_id'       => $request->employee_id,
                        'date'              => $request->date ?: today()->toDateString(),
                        'station_id'        => $request->station_id,
                        'machine_id'        => $machine->id,
                        'gun_id'            => $this->cleanFk($request->gun_id[$row['index']] ?? null),
                        'start_counter'     => $row['start'],
                        'end_counter'       => $row['end'],
                        'net'               => $row['net'],
                        'is_rollover'       => $row['isRollover'],
                        'requires_approval' => $row['requiresApproval'],
                        'approval_status'   => $row['requiresApproval'] ? 'pending' : null,
                        'price'             => $row['price'],
                        'total'             => $row['net'] * $row['price'],
                    ];

                    // الخصم الفوري فقط للصفوف المقبولة تلقائياً
                    if (!$row['requiresApproval'] && $machine->stock_id) {
                        $stock = Stock::find($machine->stock_id);
                        if ($stock) {
                            $stock->update(['qty' => $stock->qty - $row['net']]);
                        }
                    }
                }
                MachineDetail::insert($data);
            });
        } catch (\Exception $e) {
            \Log::error('MachineDetail store failed: ' . $e->getMessage());
            return back()->withErrors('خطأ في حفظ البيانات: ' . $e->getMessage())->withInput();
        }

        return back()->with('success', 'تم ادخال العدادات بنجاح');
    }

    public function edit(MachineDetail $machine_detail)
    {
        $machine_detail = $machine_detail->load('station');
        // ماكينات نفس المحطة فقط، مع البير (لنوع الوقود)، مرتبة طبيعياً حسب الاسم
        $machines = Machine::where('station_id', $machine_detail->station_id)
            ->with('stock')
            ->get()
            ->sortBy(fn($m) => (string) $m->name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        $employees = Employee::where('station_id', $machine_detail->station_id)->get();
        $guns = Gun::where('station_id', $machine_detail->station_id)->where('machine_id', $machine_detail->machine_id)->get();
        return view('machine_detail_edit', compact('machine_detail', 'machines', 'employees', 'guns'));
    }

    public function update(MachineDetail $machine_detail, Request $request)
    {
        $station_id = $machine_detail->station_id;

        // هل الصف الأصلي كان مخصوماً من المخزون؟
        $originalWasDeducted = !($machine_detail->requires_approval || $machine_detail->approval_status === 'pending');

        // المرحلة 1: حساب جميع القراءات الجديدة
        $rows = [];
        foreach ($request->machine_id as $index => $machine_id) {
            if (!$machine_id) continue;

            $machine = Machine::find($machine_id);
            if (!$machine) {
                return back()->withErrors('الماكينة غير موجودة في الصف رقم ' . ($index + 1));
            }

            $start = $this->parseCounter($request->start_counter[$index] ?? '0');
            $end = $this->parseCounter($request->end_counter[$index] ?? '0');
            $price = (float) str_replace(',', '', $request->price[$index] ?? '0');

            try {
                [$net, $isRollover, $requiresApproval] = $this->computeReading(
                    $start,
                    $end,
                    (float) $machine->max_counter,
                    $machine->name,
                    (bool) ($machine->use_rollover ?? true),
                    $machine->allowed_rollover !== null ? (float) $machine->allowed_rollover : null
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                return $e->getResponse()->withInput();
            }

            $rows[] = [
                'index'            => $index,
                'machine'          => $machine,
                'start'            => $start,
                'end'              => $end,
                'price'            => $price,
                'net'              => $net,
                'isRollover'       => $isRollover,
                'requiresApproval' => $requiresApproval,
            ];
        }

        if (empty($rows)) {
            return back()->withErrors('لم يتم إدخال أي بيانات');
        }

        // المرحلة 2: حساب صافي التغيير لكل بير (الجديد − الأصلي)
        $stockChanges = []; // stock_id => net change
        foreach ($rows as $row) {
            $machine = $row['machine'];
            if (!$machine->stock_id) continue;
            if (!isset($stockChanges[$machine->stock_id])) {
                $stockChanges[$machine->stock_id] = 0;
            }
            // الصفوف غير المعلقة تُخصم
            if (!$row['requiresApproval']) {
                $stockChanges[$machine->stock_id] += $row['net'];
            }
        }

        // تعويض الصف الأصلي إذا كان مخصوماً
        if ($originalWasDeducted && $machine_detail->machine) {
            $origStockId = $machine_detail->machine->stock_id;
            if ($origStockId) {
                if (!isset($stockChanges[$origStockId])) {
                    $stockChanges[$origStockId] = 0;
                }
                // نطرح الأصلي لأننا سنضيف الجديد: net_change = new - old
                $stockChanges[$origStockId] -= $machine_detail->net;
            }
        }

        // فحص الرصيد: هل سيصبح أي بير بالسالب؟
        foreach ($stockChanges as $stockId => $change) {
            if ($change <= 0) continue;
            $stock = Stock::find($stockId);
            if ($stock && $change > $stock->qty) {
                return back()->withErrors(
                    'الكمية المطلوبة من بير "' . $stock->name . '" (' . number_format($change) . ' لتر) تتجاوز الرصيد المتوفر (' . number_format($stock->qty) . ' لتر)'
                )->withInput();
            }
        }

        // المرحلة 3: حفظ كل شيء في transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $request, $machine_detail, $originalWasDeducted) {
            // حذف الصف الأصلي
            if ($originalWasDeducted) {
                $origMachine = Machine::find($machine_detail->machine_id);
                if ($origMachine && $origMachine->stock_id) {
                    $stock = Stock::find($origMachine->stock_id);
                    if ($stock) {
                        $stock->update(['qty' => $stock->qty + $machine_detail->net]);
                    }
                }
            }
            $machine_detail->delete();

            // إدراج الصفوف الجديدة وخصم المخزون
            $data = [];
            foreach ($rows as $row) {
                $machine = $row['machine'];
                $data[] = [
                    'date'              => $request->date ?? today(),
                    'employee_id'       => $request->employee_id,
                    'station_id'        => $request->station_id,
                    'machine_id'        => $machine->id,
                    'gun_id'            => $this->cleanFk($request->gun_id[$row['index']] ?? null),
                    'start_counter'     => $row['start'],
                    'end_counter'       => $row['end'],
                    'net'               => $row['net'],
                    'is_rollover'       => $row['isRollover'],
                    'requires_approval' => $row['requiresApproval'],
                    'approval_status'   => $row['requiresApproval'] ? 'pending' : null,
                    'price'             => $row['price'],
                    'total'             => $row['net'] * $row['price'],
                ];

                if (!$row['requiresApproval'] && $machine->stock_id) {
                    $stock = Stock::find($machine->stock_id);
                    if ($stock) {
                        $stock->update(['qty' => $stock->qty - $row['net']]);
                    }
                }
            }
            MachineDetail::insert($data);
        });

        return redirect()->route('machine_detail.index', $station_id)->with('success', 'تم تحديث العدادات بنجاح');
    }

    public function destroy(MachineDetail $machine_detail)
    {
        $machine = Machine::find($machine_detail->machine_id);

        // الصفوف المعلقة لم تُخصم من المخزون أصلاً — لا تعويض عند حذفها
        if (!($machine_detail->requires_approval || $machine_detail->approval_status === 'pending')) {
            $stock = Stock::find($machine->stock_id ?? null);

            if ($stock) {
                $stock->update([
                    'qty' => $stock->qty + $machine_detail->net,
                ]);
            }
        }

        $machine_detail->delete();
        return back()->with('success', 'تم حذف العداد بنجاح');
    }

    /**
     * قائمة القراءات المعلقة بانتظار الاعتماد (عبر المحطات)
     */
    public function pending()
    {
        if (!Auth::user()->can('machine_details.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد القراءات');
        }

        $userStationIds = Auth::user()->stations()->pluck('station_id')->toArray();

        $pending = MachineDetail::with(['machine', 'gun', 'station', 'employee'])
            ->where('approval_status', 'pending')
            ->whereIn('station_id', $userStationIds)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return view('pending_machine_details', compact('pending'));
    }

    /**
     * اعتماد قراءة معلقة — ينفّص الخصم المؤجل من المخزون
     */
    public function approve(MachineDetail $machine_detail)
    {
        if (!Auth::user()->can('machine_details.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد القراءات');
        }

        // idempotent: الصف ليس معلقاً (معتمد/مرفوض/عادي) = لا شيء يُنفذ
        if ($machine_detail->approval_status !== 'pending') {
            return back()->with('success', 'الصف تمت معالجته مسبقاً');
        }

        $machine = Machine::find($machine_detail->machine_id);
        $stock = $machine && $machine->stock_id ? Stock::find($machine->stock_id) : null;

        if ($stock && $machine_detail->net > $stock->qty) {
            return back()->with('error', 'لا يمكن الاعتماد: الكمية (' . number_format($machine_detail->net) . ' لتر) تتجاوز رصيد البير "' . $stock->name . '" المتوفر (' . number_format($stock->qty) . ' لتر)');
        }

        $machine_detail->update([
            'approval_status' => 'approved',
            'approved_by'     => Auth::id(),
            'approved_at'     => now(),
        ]);

        if ($stock) {
            $stock->update(['qty' => $stock->qty - $machine_detail->net]);
        }

        return back()->with('success', 'تم اعتماد القراءة وخصم الكمية من البير بنجاح');
    }

    /**
     * رفض قراءة معلقة — لا يخصم أي كمية أبداً
     */
    public function reject(MachineDetail $machine_detail)
    {
        if (!Auth::user()->can('machine_details.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد القراءات');
        }

        if ($machine_detail->approval_status !== 'pending') {
            return back()->with('success', 'الصف تمت معالجته مسبقاً');
        }

        $machine_detail->update([
            'approval_status' => 'rejected',
            'approved_by'     => Auth::id(),
            'approved_at'     => now(),
        ]);

        return back()->with('success', 'تم رفض القراءة');
    }
}
