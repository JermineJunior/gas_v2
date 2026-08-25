<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Employee;
use App\Models\ExpenseDetail;
use App\Models\MachineDetail;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositDetailController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }
        $deposits = Deposit::with(['deposit_details', 'station'])
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

    /**
     * ملخص مطابقة الوردية لمحطة وتاريخ — للعرض فقط، بدون أي تحقق أو منع.
     */
    public function shiftSummary(Request $request)
    {
        $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
            'date'       => 'required|date',
        ]);

        return response()->json(
            MachineDetail::shiftSummary((int) $request->station_id, $request->date)
        );
    }

    /**
     * نموذج الرصيد المرحَّل لكل موظف/محطة:
     * القديم الخام (آخر متبقٍ) + الجديد (القراءات غير المسوّاة) − التوريد المُدخل − مصروفات نفس التواريخ
     * المتبقي الناتج هو ما يُقرأ كـ"قديم" في التصفية التالية، والقراءات المحسوبة تُسوّى (status 0→1)
     * حتى لا تُعَدّ مرة أخرى. السيرفر هو المرجع: قيم العميل القديم/الجديد/المتبقي تُهمل.
     */
    public function store(Request $request)
    {
        $stationId  = $request->station_id;
        $employeeId = $request->employee_id;

        // القراءات غير المسوّاة لهذا الموظف في هذه المحطة (كل الأيام المتراكمة)
        $unsettledMachines = MachineDetail::where('station_id', $stationId)
            ->where('employee_id', $employeeId)
            ->where('status', 0)
            ->get();

        $totalNewMachine = (float) $unsettledMachines->sum('total');
        $unsettledDates  = $unsettledMachines->pluck('date')->unique()->all();

        // المصروفات تُطابق بتقاطع التواريخ مع القراءات غير المسوّاة فقط (بلا employee_id ولا حالة تسوية)
        $expensesTotal = $unsettledDates
            ? (float) ExpenseDetail::whereHas('expense', function ($q) use ($stationId, $unsettledDates) {
                $q->where('station_id', $stationId)
                  ->whereIn('date', $unsettledDates);
            })->sum('expense_amount')
            : 0.0;

        // القيمة المرحَّلة من آخر توريد سابق لنفس الموظف/المحطة
        $rawOldMachine = (float) (Deposit::where('station_id', $stationId)
            ->where('employee_id', $employeeId)
            ->latest()
            ->first()
            ->remaining ?? 0);

        $depositAmountSum = collect($request->deposit_amount ?? [])
            ->sum(fn ($a) => (float) str_replace(',', '', $a));

        // القديم المعروض = المرحَّل − التوريد المُدخل − المصروفات (قد يكون سالباً — حالة صحيحة تعني أن
        // التوريد غطى أكثر من الرصيد القديم وحده)، والمتبقي = القديم المعروض + الجديد
        $oldDisplayed = $rawOldMachine - $depositAmountSum - $expensesTotal;
        $remaining    = $oldDisplayed + $totalNewMachine;

        DB::transaction(function () use ($request, $unsettledMachines, $totalNewMachine, $oldDisplayed, $remaining, $expensesTotal) {
            $deposit = Deposit::create([
                'station_id'         => $request->station_id,
                'date'               => $request->date,
                'employee_id'        => $request->employee_id,
                'total_new_machine'  => $totalNewMachine,
                'total_old_machine'  => $oldDisplayed,
                'remaining'          => $remaining,
                'expenses_total'     => $expensesTotal,
            ]);

            $data = [];
            foreach ($request->deposit_amount as $index => $amount) {
            $data[] = [
                'date'           => $request->date,
                'deposit_id'     => $deposit->id,
                'station_id'     => $request->station_id,
                // تطبيع دفاعي: قد تصل القيم بفواصل التنسيق إن تجاوز عميل جافاسكربت
                'deposit_amount' => str_replace(',', '', $amount),
                'deposit_desc'   => $request->deposit_desc[$index],
            ];
            }

            if (!empty($data)) {
                DepositDetail::insert($data);
            }

            // ── تسوية القراءات المحسوبة الآن: status 0→1 حتى لا يعيدها get_remaining مستقبلاً ──
            if ($unsettledMachines->isNotEmpty()) {
                MachineDetail::whereIn('id', $unsettledMachines->pluck('id'))
                    ->update(['status' => 1]);
            }
        });

        return back()->with('success', 'تم ادخال التوريدات بنجاح');
    }

    public function edit(Deposit $deposit)
    {
        $deposit = $deposit->load(['station', 'deposit_details']);
        $station = $deposit->station;
        $employees = Employee::where('station_id',$station->id)->get();

        // إعادة بناء القديم الخام المرحَّل: المعروض المخزّن + بنود هذا التوريد + مصروفاته المخزّنة
        $rawOldMachine = (float) $deposit->total_old_machine
            + (float) $deposit->deposit_details->sum('deposit_amount')
            + (float) ($deposit->expenses_total ?? 0);
        $formExpenses = (float) ($deposit->expenses_total ?? 0);

        // قراءات غير مسوّاة ظهرت بعد إنشاء التوريد — سيضمّها الحفظ القادم ويُسويها (عرض مسبق هنا)
        $freshUnsettled = MachineDetail::where('station_id', $deposit->station_id)
            ->where('employee_id', $deposit->employee_id)
            ->where('status', 0)->get();
        $freshDates = $freshUnsettled->pluck('date')->unique()->all();
        $freshSum = (float) $freshUnsettled->sum('total');
        $freshExpenses = $freshDates
            ? (float) ExpenseDetail::whereHas('expense', function ($q) use ($deposit, $freshDates) {
                $q->where('station_id', $deposit->station_id)
                  ->whereIn('date', $freshDates);
            })->sum('expense_amount')
            : 0.0;

        return view('deposit_detail_edit', compact('deposit', 'employees', 'rawOldMachine', 'formExpenses', 'freshSum', 'freshExpenses'));
    }

    public function update(Deposit $deposit, Request $request)
    {
        // مصفوفة معرفات البنود المرسلة (فارغة أو غائبة = بند جديد)
        $submittedIds = collect($request->input('detail_ids', []))
            ->map(fn($v) => $v ? (int) $v : null);
        $existingDetails = $deposit->deposit_details->keyBy('id');

        // ── فحص التعارض مع البنود المعتمدة قبل تطبيق أي تغيير ──
        $conflicts = [];
        foreach ($existingDetails as $id => $detail) {
            if ($detail->status != 1) {
                continue;
            }

            if (!$submittedIds->contains($id)) {
                $conflicts[] = "لا يمكن حذف البند المعتمد رقم #{$id} — قيمته " . number_format($detail->deposit_amount);
                continue;
            }

            $index = $submittedIds->search($id);
            $amount = (float) str_replace(',', '', $request->deposit_amount[$index]);
            $desc = trim((string) ($request->deposit_desc[$index] ?? ''));

            if (abs($amount - (float) $detail->deposit_amount) > 0.001 || $desc !== trim((string) $detail->deposit_desc)) {
                $conflicts[] = "لا يمكن تعديل البند المعتمد رقم #{$id} — قيمته " . number_format($detail->deposit_amount);
            }
        }

        if (!empty($conflicts)) {
            return back()->withErrors(implode(' | ', $conflicts))->withInput();
        }

        // ── إعادة احتساب مكونات التصفية من قيم مخزّنة موثوقة (لا نثق بقيم العميل) ──
        // القديم الخام = المعروض المخزّن + بنود هذا التوريد قبل التعديل + مصروفاته المخزّنة
        $existingSumBefore = (float) $existingDetails->sum('deposit_amount');
        $rawOldMachine = (float) $deposit->total_old_machine
            + $existingSumBefore
            + (float) ($deposit->expenses_total ?? 0);

        // قراءات غير مسوّاة ظهرت بعد إنشاء التوريد: تُضم لهذه التصفية وتُسوّى معها
        $freshUnsettled = MachineDetail::where('station_id', $deposit->station_id)
            ->where('employee_id', $deposit->employee_id)
            ->where('status', 0)->get();
        $freshDates = $freshUnsettled->pluck('date')->unique()->all();
        $freshExpenses = $freshDates
            ? (float) ExpenseDetail::whereHas('expense', function ($q) use ($deposit, $freshDates) {
                $q->where('station_id', $deposit->station_id)
                  ->whereIn('date', $freshDates);
            })->sum('expense_amount')
            : 0.0;

        $depositAmountSum = collect($request->deposit_amount ?? [])
            ->sum(fn ($a) => (float) str_replace(',', '', $a));

        // نفس صيغة store(): القديم المعروض = الخام − التوريد − المصروفات، المتبقي = القديم المعروض + الجديد
        $expensesTotal = (float) ($deposit->expenses_total ?? 0) + $freshExpenses;
        $oldDisplayed  = $rawOldMachine - $depositAmountSum - $expensesTotal;
        $newDisplayed  = (float) $deposit->total_new_machine + (float) $freshUnsettled->sum('total');
        $remaining     = $oldDisplayed + $newDisplayed;

        DB::transaction(function () use ($request, $deposit, $freshUnsettled, $expensesTotal, $oldDisplayed, $newDisplayed, $remaining, $existingDetails) {
            // ── تحديث رأس التوريد في مكانه (بدون حذف) — بقيم محسوبة من السيرفر ──
            $deposit->update([
                'station_id'         => $request->station_id,
                'date'               => $request->date,
                'employee_id'        => $request->employee_id,
                'total_new_machine'  => $newDisplayed,
                'total_old_machine'  => $oldDisplayed,
                'remaining'          => $remaining,
                'expenses_total'     => $expensesTotal,
            ]);

            // ── مطابقة البنود: تعديل المعلق / إنشاء الجديد / تجاهل المعتمد ──
            $keptIds = [];

            foreach ($request->detail_ids as $index => $submittedId) {
                $amount = str_replace(',', '', $request->deposit_amount[$index]);
                $desc = $request->deposit_desc[$index] ?? '';

                if ($submittedId && isset($existingDetails[(int) $submittedId])) {
                    $detail = $existingDetails[(int) $submittedId];

                    if ($detail->status == 1) {
                        // بند معتمد: ثابت — لا يُعدّل ولا يُحذف
                        $keptIds[] = $detail->id;
                        continue;
                    }

                    // بند معلق: حدثه بشكل طبيعي
                    $detail->update([
                        'date'          => $request->date,
                        'station_id'    => $request->station_id,
                        'deposit_amount' => $amount,
                        'deposit_desc'  => $desc,
                    ]);
                    $keptIds[] = $detail->id;
                } else {
                    // بند جديد
                    $newDetail = DepositDetail::create([
                        'date'           => $request->date,
                        'deposit_id'     => $deposit->id,
                        'station_id'     => $request->station_id,
                        'deposit_amount' => $amount,
                        'deposit_desc'   => $desc,
                        'status'         => 0,
                    ]);
                    $keptIds[] = $newDetail->id;
                }
            }

            // ── احذف البنود المعلقة المحذوفة من النموذج فقط (المعتمدة محمية أعلاه) ──
            $deposit->deposit_details()
                ->where('status', 0)
                ->whereNotIn('id', $keptIds)
                ->delete();

            // ── تسوية القراءات الجديدة المضمّة الآن: status 0→1 حتى لا تُعَد مستقبلاً ──
            if ($freshUnsettled->isNotEmpty()) {
                MachineDetail::whereIn('id', $freshUnsettled->pluck('id'))
                    ->update(['status' => 1]);
            }
        });

        return redirect()->route('deposit_detail.index', $deposit->station_id)->with('success', 'تم تحديث  التوريدات بنجاح');
    }

    /**
     * اعتماد بند توريد — idempotent
     */
    public function approve(DepositDetail $deposit_detail)
    {
        if (!Auth::user()->can('deposits.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد التوريدات');
        }

        // الاعتماد عملية آمنة للتكرار: البند المعتمد مسبقاً = لا شيء يُنفذ
        if ($deposit_detail->status == 1) {
            return back()->with('success', 'البند معتمد بالفعل');
        }

        $deposit_detail->update([
            'status'      => 1,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد البند بنجاح');
    }

    /**
     * إلغاء اعتماد بند توريد — يعيده إلى قيد الاعتماد بدون الاحتفاظ بسجل الاعتماد السابق
     */
    public function unapprove(DepositDetail $deposit_detail)
    {
        if (!Auth::user()->can('deposits.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد التوريدات');
        }

        if ($deposit_detail->status == 0) {
            return back()->with('success', 'البند غير معتمد بالفعل');
        }

        $deposit_detail->update([
            'status'      => 0,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'تم إلغاء اعتماد البند');
    }

    public function destroy(DepositDetail $deposit_detail)
    {
        if ($deposit_detail->status == 1) {
            return back()->with('error', "لا يمكن حذف البند المعتمد رقم #{$deposit_detail->id}");
        }

        $deposit_detail->delete();
        $exists = DepositDetail::where('deposit_id',$deposit_detail->deposit_id)->exists();
        if (!$exists) {
            Deposit::where('id',$deposit_detail->deposit_id)->delete();
        }

        return back()->with('success', 'تم حذف التوريد بنجاح');
    }
}
