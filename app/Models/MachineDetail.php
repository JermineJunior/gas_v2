<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineDetail extends Model
{
    protected $fillable = ['station_id','gun_id', 'machine_id', 'start_counter', 'end_counter', 'net', 'is_rollover', 'requires_approval', 'approval_status', 'approved_by', 'approved_at', 'price', 'total','status','employee_id'];

    protected $casts = [
        'date' => 'date',
        'is_rollover' => 'boolean',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * ملخص مطابقة الوردية لمحطة وتاريخ معينين (للعرض فقط — بدون أي تحقق أو منع).
     *
     * shift_total       : SUM(machine_details.total) لليوم، باستثناء القراءات المعلقة
     *                     (approval_status = 'pending') لأنها غير مؤكدة بعد.
     *                     ملاحظة: approval_status قابل لأن يكون NULL للقراءات العادية،
     *                     و NULL != 'pending' في SQL يرجع NULL وليس TRUE — لذلك يجب
     *                     التعامل معه صراحةً بـ whereNull وإلا سقطت كل الصفوف العادية.
     * expenses_total    : SUM(expense_details.expense_amount) لبنود المصروفات التي
     *                     تتبع مصروفات نفس المحطة والتاريخ. تُحتسب كل البنود عمداً
     *                     (معلقة ومعتمدة) بخلاف قاعدة استثناء المعلق في القراءات —
     *                     لأن المصروف دفع منه الموظف فعلاً من المبلغ المحصل.
     * already_deposited : SUM(deposit_details.deposit_amount) لبنود التوريد التي تتبع
     *                     توريدات نفس المحطة والتاريخ.
     *                     ⚠ اختيار مقصود وليس سهواً: تُحتسب كل البنود بغض النظر عن
     *                     حالة اعتمادها (status = 0 أو 1) — بنفس قاعدة المصروفات —
     *                     لأن البند المسجّل يُعد مبلغاً ورّده الموظف فعلاً حتى لو لم
     *                     يُعتمد بعد، والغرض من البطاقة هو المطابقة الحية أثناء
     *                     إدخال التوريد لا مراجعة الاعتمادات.
     * net_owed          : الصافي المستحق على الموظف
     *                     = shift_total - expenses_total - already_deposited.
     */
    public static function shiftSummary(int $stationId, string $date): array
    {
        $shiftTotal = (float) self::query()
            ->where('station_id', $stationId)
            ->whereDate('date', $date)
            ->where(function ($q) {
                $q->whereNull('approval_status')
                    ->orWhere('approval_status', '!=', 'pending');
            })
            ->sum('total');

        $expensesTotal = (float) ExpenseDetail::query()
            ->whereHas('expense', function ($q) use ($stationId, $date) {
                $q->where('expenses.station_id', $stationId)
                    ->whereDate('expenses.date', $date);
            })
            ->sum('expense_amount');

        // كل البنود عمداً بغض النظر عن حالة الاعتماد — راجع التوثيق أعلاه
        $alreadyDeposited = (float) DepositDetail::query()
            ->whereHas('deposit', function ($q) use ($stationId, $date) {
                $q->where('deposits.station_id', $stationId)
                    ->whereDate('deposits.date', $date);
            })
            ->sum('deposit_amount');

        return [
            'shift_total'       => $shiftTotal,
            'expenses_total'    => $expensesTotal,
            'already_deposited' => $alreadyDeposited,
            'net_owed'          => $shiftTotal - $expensesTotal - $alreadyDeposited,
        ];
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function gun()
    {
        return $this->belongsTo(Gun::class);
    }
}
