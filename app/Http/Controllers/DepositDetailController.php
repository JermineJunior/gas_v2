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

        // ── تحديث رأس التوريد في مكانه (بدون حذف) ──
        $deposit->update([
            'station_id'         => $request->station_id,
            'date'               => $request->date,
            'employee_id'        => $request->employee_id,
            'total_new_machine'  => $request->total_new_machine,
            'total_old_machine'  => $request->total_old_machine,
            'remaining'          => $request->remaining,
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
