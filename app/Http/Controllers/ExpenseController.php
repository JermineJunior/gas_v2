<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }

        $expenses = Expense::with(['expense_details', 'user'])
            ->where('station_id', $station->id)
            ->orderBy('date', 'desc') // الأحدث أولاً
            ->get()
            ->groupBy(function (Expense $item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            })
            ->sortKeysDesc();

        return view('list_expense', compact('expenses', 'station'));
    }

    public function create(Station $station)
    {
        return view('expense_detail_create', compact('station'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'station_id'      => 'required|exists:stations,id',
            'date'            => 'required|date',
            'expense_amount'   => 'required|array|min:1',
            'expense_amount.*' => 'numeric',
            'expense_desc'     => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            $expense = Expense::create([
                'station_id'  => $request->station_id,
                'user_id'     => Auth::id(),
                'date'        => $request->date,
            ]);

            foreach ($request->expense_amount as $index => $amount) {
                $expense->expense_details()->create([
                    'date'           => $request->date,
                    'station_id'     => $request->station_id,
                    'expense_amount' => str_replace(',', '', $amount),
                    'expense_desc'   => $request->expense_desc[$index] ?? '',
                    'status'         => 0, // قيد الاعتماد
                ]);
            }
        });

        return back()->with('success', 'تم ادخال المصروفات بنجاح');
    }

    public function edit(Expense $expense)
    {
        $expense = $expense->load(['station', 'expense_details.approver']);
        return view('expense_detail_edit', compact('expense'));
    }

    public function update(Expense $expense, Request $request)
    {
        $request->validate([
            'station_id'      => 'required|exists:stations,id',
            'date'            => 'required|date',
            'expense_amount'   => 'required|array|min:1',
            'expense_amount.*' => 'numeric',
            'expense_desc'     => 'nullable|array',
        ]);

        // مصفوفة معرفات البنود المرسلة (فارغة أو غائبة = بند جديد)
        $submittedIds = collect($request->input('detail_ids', []))
            ->map(fn($v) => $v ? (int) $v : null);
        $existingDetails = $expense->expense_details->keyBy('id');

        // ── فحص التعارض مع البنود المعتمدة قبل تطبيق أي تغيير ──
        $conflicts = [];
        foreach ($existingDetails as $id => $detail) {
            if ($detail->status != 1) {
                continue;
            }

            if (!$submittedIds->contains($id)) {
                $conflicts[] = "لا يمكن حذف البند المعتمد رقم #{$id} — قيمته " . number_format($detail->expense_amount);
                continue;
            }

            $index = $submittedIds->search($id);
            $amount = (float) str_replace(',', '', $request->expense_amount[$index]);
            $desc = trim((string) ($request->expense_desc[$index] ?? ''));

            if (abs($amount - (float) $detail->expense_amount) > 0.001 || $desc !== trim((string) $detail->expense_desc)) {
                $conflicts[] = "لا يمكن تعديل البند المعتمد رقم #{$id} — قيمته " . number_format($detail->expense_amount);
            }
        }

        if (!empty($conflicts)) {
            return back()->withErrors(implode(' | ', $conflicts))->withInput();
        }

        DB::transaction(function () use ($expense, $request, $submittedIds, $existingDetails) {
            // ── تحديث رأس المصروف في مكانه (بدون حذف) ──
            $expense->update([
                'station_id' => $request->station_id,
                'date'       => $request->date,
            ]);

            // ── مطابقة البنود: تعديل المعلق / إنشاء الجديد / تجاهل المعتمد ──
            $keptIds = [];

            foreach ($request->input('detail_ids', []) as $index => $submittedId) {
                $amount = str_replace(',', '', $request->expense_amount[$index]);
                $desc = $request->expense_desc[$index] ?? '';

                if ($submittedId && isset($existingDetails[(int) $submittedId])) {
                    $detail = $existingDetails[(int) $submittedId];

                    if ($detail->status == 1) {
                        // بند معتمد: ثابت — لا يُعدّل ولا يُحذف
                        $keptIds[] = $detail->id;
                        continue;
                    }

                    // بند معلق: حدثه بشكل طبيعي
                    $detail->update([
                        'date'           => $request->date,
                        'station_id'     => $request->station_id,
                        'expense_amount' => $amount,
                        'expense_desc'   => $desc,
                    ]);
                    $keptIds[] = $detail->id;
                } else {
                    // بند جديد
                    $newDetail = $expense->expense_details()->create([
                        'date'           => $request->date,
                        'station_id'     => $request->station_id,
                        'expense_amount' => $amount,
                        'expense_desc'   => $desc,
                        'status'         => 0,
                    ]);
                    $keptIds[] = $newDetail->id;
                }
            }

            // ── احذف البنود المعلقة المحذوفة من النموذج فقط (المعتمدة محمية أعلاه) ──
            $expense->expense_details()
                ->where('status', 0)
                ->whereNotIn('id', $keptIds)
                ->delete();
        });

        return redirect()->route('expense.index', $expense->station_id)->with('success', 'تم تحديث المصروفات بنجاح');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->expense_details()->where('status', 1)->exists()) {
            return back()->with('error', 'لا يمكن حذف المصروف لانه يحتوي على بنود معتمدة، ألغِ الاعتماد أولاً');
        }

        $expense->delete(); // حذف متسلسل للبنود عبر قيد قاعدة البيانات

        return back()->with('success', 'تم حذف المصروف بنجاح');
    }

    /**
     * اعتماد بند مصروف — idempotent
     */
    public function approve(ExpenseDetail $expense_detail)
    {
        if (!Auth::user()->can('expenses.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد المصروفات');
        }

        // الاعتماد عملية آمنة للتكرار: البند المعتمد مسبقاً = لا شيء يُنفذ
        if ($expense_detail->status == 1) {
            return back()->with('success', 'البند معتمد بالفعل');
        }

        $expense_detail->update([
            'status'      => 1,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد البند بنجاح');
    }

    /**
     * إلغاء اعتماد بند مصروف — يعيده إلى قيد الاعتماد بدون الاحتفاظ بسجل الاعتماد السابق
     */
    public function unapprove(ExpenseDetail $expense_detail)
    {
        if (!Auth::user()->can('expenses.approve')) {
            return back()->with('error', 'غير مصرح لك باعتماد المصروفات');
        }

        if ($expense_detail->status == 0) {
            return back()->with('success', 'البند غير معتمد بالفعل');
        }

        $expense_detail->update([
            'status'      => 0,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'تم إلغاء اعتماد البند');
    }
}
