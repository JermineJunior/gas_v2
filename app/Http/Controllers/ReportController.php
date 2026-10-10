<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DepositDetail;
use App\Models\Detail;
use App\Models\Employee;
use App\Models\ExpenseDetail;
use App\Models\FuelDeliviery;
use App\Models\FuelOrder;
use App\Models\MachineDetail;
use App\Models\Stock;
use App\Models\Station;
use App\Models\Supplier;
use App\Models\Deposit;
use App\Models\Tuncker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Determine which stations the current user is allowed to see.
     * Scoping is by the station_user pivot only — no role is exempt.
     * A submitted station_id must belong to the user (403 otherwise);
     * with no selection the report spans all of their stations.
     *
     * @return array<int>
     */
    private function resolveStationIds(Request $request): array
    {
        $userStationIds = Auth::user()->stations->pluck('id')->map(fn($id) => (int) $id)->all();

        if ($request->filled('station_id')) {
            if (!in_array((int) $request->station_id, $userStationIds, true)) {
                abort(403, 'ليس لديك صلاحية الوصول لهذه المحطة');
            }
            return [(int) $request->station_id];
        }

        return $userStationIds;
    }

    public function deposit_detail()
    {
        $stations = Auth::user()->stations;
        return view('deposit_detail', compact('stations'));
    }

    public function deposit_detail_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $stationIds = $this->resolveStationIds($request);
        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        $operations = DepositDetail::with('deposit.employee')
            ->whereIn('station_id', $stationIds)
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('deposit_detail_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function machine_detail()
    {
        $stations = Auth::user()->stations;
        return view('machine_detail', compact('stations'));
    }

    public function machine_detail_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $stationIds = $this->resolveStationIds($request);
        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        $operations = MachineDetail::whereIn('station_id', $stationIds)
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->orderBy('date')
            ->orderBy('id')
            ->get();
        return view('machine_detail_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function tuncker()
    {
        $stations = Auth::user()->stations;
        return view('tuncker_detail', compact('stations'));
    }

    public function tuncker_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $stationIds = $this->resolveStationIds($request);
        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        $operations = Tuncker::whereIn('station_id', $stationIds)
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();
        return view('tuncker_result', compact('operations', 'start_date', 'end_date', 'station'));
    }

    public function supplier()
    {
        $suppliers = Supplier::get();
        return view('supplier_detail', compact('suppliers'));
    }

    public function supplier_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $supplier = Supplier::find($request->supplier_id) ?? null;

        $orders = FuelOrder::with('supplier')
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // نوع الوقود وسعر اللتر والتكلفة للتوريدات تأتي من أمر التوريد المرتبط بها
        // فالتسليم أصبح مرتبطًا بأمر عبر fuel_order_id
        $deliveries = FuelDeliviery::with('supplier', 'station', 'fuelOrder')
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $total_requested = $orders->sum('quantity');
        $total_delivered = $deliveries->sum('quantity');

        // قيمة الطلبات بالجنيه (من حقل الإجمالي المحفوظ) وتكلفة التوريدات (كمية × سعر اللتر من أمر التوريد)
        $total_requested_value = $orders->sum(fn($order) => (float) $order->total);
        $total_delivered_value = $deliveries->sum(fn($delivery) => $delivery->fuelOrder
            ? (float) $delivery->quantity * (float) $delivery->fuelOrder->price_per_liter
            : 0);

        return view('supplier_result', compact('orders', 'deliveries', 'total_requested', 'total_delivered', 'total_requested_value', 'total_delivered_value', 'start_date', 'end_date', 'supplier'));
    }

    public function debt()
    {
        /*  $clients = Client::when(Auth::user()->type == 3, function ($q) {
            $q->where('user_id', Auth::id());
        })->get(); */ // no need for this check , after switching to roles and permissions
        $clients = Client::where('user_id', Auth::id())->get();
        return view('debt', compact('clients'));
    }

    public function debt_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $client = Client::find($request->client_id) ?? null;
        $operations = Detail::with('client')
            ->when(Auth::user()->type == 3, function ($q) {
                return $q->where('user_id', Auth::id());
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->end_date);
            })
            ->get();

        // ── إجماليات أسفل التقرير: المدفوع (الإيراد) والمتبقي (الإجمالي − الإيراد) ──
        $totalAmount  = $operations->sum('amount');
        $totalBill    = $operations->sum('total');
        $totalRemaining = $totalBill - $totalAmount;

        return view('debt_result', compact('operations', 'start_date', 'end_date', 'client', 'totalAmount', 'totalBill', 'totalRemaining'));
    }

    // ── Machine Report (no time filter) ──
    public function machine_report()
    {

        $stations = Auth::user()->stations; // only show the user stations
        return view('machine_report', compact('stations'));
    }

    public function machine_report_result(Request $request)
    {
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;
        $stationIds = $this->resolveStationIds($request);

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        $machinesQuery->whereIn('station_id', $stationIds);
        if ($machineId) $machinesQuery->where('id', $machineId);
        if ($fuelType) $machinesQuery->whereHas('stock', fn($q) => $q->where('type', $fuelType));
        $machines = $machinesQuery->get();

        $results = $machines->map(function ($machine) {
            $gunStats = \App\Models\MachineDetail::select('gun_id', DB::raw('SUM(net) as total_net'), DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as count'))
                ->where('machine_id', $machine->id)
                ->where(function ($q) {
                    $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending');
                })
                ->groupBy('gun_id')
                ->get()
                ->map(function ($row) {
                    $gun = \App\Models\Gun::find($row->gun_id);
                    return [
                        'gun_name' => $gun->name ?? '-',
                        'total_net' => $row->total_net,
                        'total_amount' => $row->total_amount,
                        'count' => $row->count,
                    ];
                });

            $totalNet = $gunStats->sum('total_net');
            $totalAmount = $gunStats->sum('total_amount');
            $stock = $machine->stock;

            return [
                'machine' => $machine,
                'guns' => $gunStats,
                'total_net' => $totalNet,
                'total_amount' => $totalAmount,
                'stock_name' => $stock->name ?? '-',
                'stock_type' => $stock ? ($stock->type == 1 ? 'جازولين' : 'بنزين') : '-',
                'stock_remaining' => $stock->qty ?? 0,
            ];
        });

        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        return view('machine_report_result', compact('results', 'station'));
    }

    // ── تقرير حساب الموظف (العداد القديم/الجديد + كل التوريدات) ──
    public function employee_account()
    {
        $stations = Auth::user()->stations;
        return view('employee_account', compact('stations'));
    }

    public function employee_account_result(Request $request)
    {
        $request->validate([
            'station_id'  => 'required|exists:stations,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        // المحطة إلزامية هنا، لذا تعيد الدالة محطة واحدة مُتحقق منها (أو 403)
        $this->resolveStationIds($request);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $employee = \App\Models\Employee::findOrFail($request->employee_id);
        $station = Station::findOrFail($request->station_id);

        // المطلوب من العداد الجديد: مجموع قراءات الموظف غير المسجلة (نفس منطق deposit_detail_create)
        $totalNewMachine = MachineDetail::where('station_id', $request->station_id)
            ->where('employee_id', $request->employee_id)
            ->where('status', 0)
            ->sum('total');

        // المطلوب من العداد القديم: متبقي آخر توريد للموظف
        $totalOldMachine = Deposit::where('station_id', $request->station_id)
            ->where('employee_id', $request->employee_id)
            ->latest()
            ->first()->remaining ?? 0;

        // كل التوريدات
        $depositsQuery = DepositDetail::whereHas('deposit', function ($q) use ($request) {
            $q->where('station_id', $request->station_id)
                ->where('employee_id', $request->employee_id);
        })
            ->with(['deposit.employee', 'approver']);
        if ($startDate) $depositsQuery->whereDate('date', '>=', $startDate);
        if ($endDate) $depositsQuery->whereDate('date', '<=', $endDate);
        $deposits = $depositsQuery->orderBy('date')->orderBy('id')->get();

        $totalDeposits = $deposits->sum('deposit_amount');
        // المتبقي = العداد القديم + العداد الجديد
        // (العداد القديم محسوب أصلاً من متبقي آخر توريد، فلا يُطرح مجموع التوريدات مرة أخرى)
        $remaining = $totalOldMachine + $totalNewMachine;

        return view('employee_account_result', compact(
            'employee',
            'station',
            'totalNewMachine',
            'totalOldMachine',
            'deposits',
            'totalDeposits',
            'remaining',
            'startDate',
            'endDate'
        ));
    }

    // ── تقرير المصروفات (تفصيلي) ──
    public function expense_list()
    {
        $stations = Auth::user()->stations;
        return view('expense_report', compact('stations'));
    }

    public function expense_list_result(Request $request)
    {
        $request->validate(['station_id' => 'required|exists:stations,id']);
        $this->resolveStationIds($request); // 403 if the station isn't theirs

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = \App\Models\ExpenseDetail::whereHas('expense', function ($q) use ($request) {
            $q->where('station_id', $request->station_id)
                ->when($request->user_id, fn($u) => $u->where('user_id', $request->user_id));
        })
            ->with(['expense.user', 'approver']);

        if ($startDate) $query->whereDate('date', '>=', $startDate);
        if ($endDate) $query->whereDate('date', '<=', $endDate);

        $details = $query->orderBy('date')->orderBy('id')->get();
        $total = $details->sum('expense_amount');
        $totalApproved = $details->where('status', 1)->sum('expense_amount');
        $totalPending = $details->where('status', 0)->sum('expense_amount');

        return view('expense_report_result', compact(
            'details',
            'total',
            'totalApproved',
            'totalPending',
            'startDate',
            'endDate'
        ));
    }

    // ── ملخص المصروفات (حسب المستخدم والشهر) ──
    public function expense_summary()
    {
        $stations = Auth::user()->stations;
        return view('expense_summary', compact('stations'));
    }

    public function expense_summary_result(Request $request)
    {
        $request->validate(['station_id' => 'required|exists:stations,id']);
        $this->resolveStationIds($request); // 403 if the station isn't theirs

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $baseQuery = \App\Models\ExpenseDetail::query()
            ->join('expenses', 'expenses.id', '=', 'expense_details.expense_id')
            ->where('expenses.station_id', $request->station_id)
            ->when($startDate, fn($q) => $q->whereDate('expense_details.date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('expense_details.date', '<=', $endDate));

        // حسب الشهر (أعمدة مجمعة فقط لتجنب خطأ ONLY_FULL_GROUP_BY)
        $byMonth = (clone $baseQuery)
            ->groupBy(DB::raw("DATE_FORMAT(expense_details.date, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(expense_details.date, '%Y-%m') as month, SUM(expense_details.expense_amount) as total")
            ->orderBy('month')
            ->get();

        $grandTotal = $byMonth->sum('total');
        $station = Station::find($request->station_id);

        return view('expense_summary_result', compact(
            'byMonth',
            'grandTotal',
            'station',
            'startDate',
            'endDate'
        ));
    }

    // ── Machine Report with Time Filter ──
    public function machine_report_time()
    {
        $stations = Auth::user()->stations;
        return view('machine_report_time', compact('stations'));
    }

    public function machine_report_time_result(Request $request)
    {
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $stationIds = $this->resolveStationIds($request);

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        $machinesQuery->whereIn('station_id', $stationIds);
        if ($machineId) $machinesQuery->where('id', $machineId);
        if ($fuelType) $machinesQuery->whereHas('stock', fn($q) => $q->where('type', $fuelType));
        $machines = $machinesQuery->get();

        $results = $machines->map(function ($machine) use ($startDate, $endDate) {
            $detailQuery = \App\Models\MachineDetail::select('gun_id', DB::raw('SUM(net) as total_net'), DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as count'))
                ->where('machine_id', $machine->id)
                ->where(function ($q) {
                    $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending');
                });
            if ($startDate) $detailQuery->whereDate('date', '>=', $startDate);
            if ($endDate) $detailQuery->whereDate('date', '<=', $endDate);
            $gunStats = $detailQuery->groupBy('gun_id')->get()->map(function ($row) {
                $gun = \App\Models\Gun::find($row->gun_id);
                return [
                    'gun_name' => $gun->name ?? '-',
                    'total_net' => $row->total_net,
                    'total_amount' => $row->total_amount,
                    'count' => $row->count,
                ];
            });

            $totalNet = $gunStats->sum('total_net');
            $totalAmount = $gunStats->sum('total_amount');
            $stock = $machine->stock;

            return [
                'machine' => $machine,
                'guns' => $gunStats,
                'total_net' => $totalNet,
                'total_amount' => $totalAmount,
                'stock_name' => $stock->name ?? '-',
                'stock_type' => $stock ? ($stock->type == 1 ? 'جازولين' : 'بنزين') : '-',
                'stock_remaining' => $stock->qty ?? 0,
            ];
        });

        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        return view('machine_report_time_result', compact('results', 'station', 'startDate', 'endDate'));
    }

    // ── General Stock Report (per stock, outside machines) ──
    public function stock_report()
    {
        $stations = Auth::user()->stations;
        return view('stock_report', compact('stations'));
    }

    public function stock_report_result(Request $request)
    {
        $stationIds = $this->resolveStationIds($request); // will abort(403) if they picked a station that isn't theirs
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stocksQuery = \App\Models\Stock::query()->with('station');
        $stocksQuery->whereIn('station_id', $stationIds);
        if ($fuelType) $stocksQuery->where('type', $fuelType);
        $stocks = $stocksQuery->get();

        $results = $stocks->map(function ($stock) use ($startDate, $endDate) {
            // الاضافات: من التناكر (stock_details بلا تاريخ - إجمالي كلي)
            $additions = \App\Models\StockDetail::where('stock_id', $stock->id)->sum('qty');

            // المسحوبات: عبر الماكينات المرتبطة بالبير
            $withdrawQuery = MachineDetail::whereHas('machine', function ($q) use ($stock) {
                $q->where('stock_id', $stock->id);
            });
            if ($startDate) $withdrawQuery->whereDate('date', '>=', $startDate);
            if ($endDate) $withdrawQuery->whereDate('date', '<=', $endDate);

            $withdrawals = (clone $withdrawQuery)->sum('net');
            $withdrawTotal = (clone $withdrawQuery)->sum('total');

            return [
                'stock' => $stock,
                'additions' => $additions,
                'withdrawals' => $withdrawals,
                'withdraw_total' => $withdrawTotal,
                'remaining' => $stock->qty,
            ];
        });

        $station = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;
        return view('stock_report_result', compact('results', 'station', 'startDate', 'endDate'));
    }

    // ── Per-Stock Movement Report (single stock detail) ──
    public function stock_movement()
    {
        $stations = Auth::user()->stations;
        return view('stock_movement', compact('stations'));
    }

    public function stock_movement_result(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stock = \App\Models\Stock::with('station')->findOrFail($request->stock_id);

        // البير يجب أن يكون تابعًا لإحدى محطات المستخدم (station_id المُرسل مجرد فلتر للعرض)
        if (!in_array((int) $stock->station_id, $this->resolveStationIds($request), true)) {
            abort(403, 'ليس لديك صلاحية الوصول لهذه المحطة');
        }

        // الاضافات: من التناكر (بلا تاريخ في stock_details)
        $additions = \App\Models\StockDetail::where('stock_id', $stock->id)
            ->get()
            ->map(function ($detail) {
                $tuncker = \App\Models\Tuncker::find($detail->tuncker_id);
                return [
                    'qty' => $detail->qty,
                    'driver_name' => $tuncker->driver_name ?? '-',
                    'tuncker_no' => $tuncker->tuncker_no ?? '-',
                    'supplier' => $tuncker->supplier->name ?? '-',
                ];
            });

        // المسحوبات: قراءات الماكينات المرتبطة بالبير
        $withdrawQuery = MachineDetail::with(['machine', 'gun'])
            ->whereHas('machine', function ($q) use ($stock) {
                $q->where('stock_id', $stock->id);
            });
        if ($startDate) $withdrawQuery->whereDate('date', '>=', $startDate);
        if ($endDate) $withdrawQuery->whereDate('date', '<=', $endDate);
        $withdrawals = $withdrawQuery->orderBy('date')->orderBy('id')->get();

        $totalAdditions = $additions->sum('qty');
        $totalWithdrawals = $withdrawals->sum('net');
        $totalWithdrawAmount = $withdrawals->sum('total');

        return view('stock_movement_result', compact(
            'stock',
            'additions',
            'withdrawals',
            'totalAdditions',
            'totalWithdrawals',
            'totalWithdrawAmount',
            'startDate',
            'endDate'
        ));
    }

    public function complete_report()
    {
        $stations = Auth::user()->stations;

        // موظفو كل محطات الحساب — تُصفّى القائمة في الصفحة حسب المحطة المختارة
        $employees = Employee::whereIn('station_id', $stations->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('complete_report', compact('stations', 'employees'));
    }

    /**
     * التقرير المتكامل: قراءات العدادات + إجمالي المصروفات + إجمالي التوريدات + رصيد الأبر.
     * كل الأقسام تُصفّى بنفس قواعد الصلاحيات (resolveStationIds) وتُصفّى بالتاريخ.
     * قائمة القراءات مُرقّمة لتجنب جلب آلاف الصفوف مرة واحدة؛ روابط الصفحات تحمل
     * الفلاتر عبر $appends (لذلك المسار يقبل GET و POST). المصروفات والتوريدات إجمالي فقط.
     */

    public function complete_report_result(Request $request)
    {
        $validated = $request->validate([
            'station_id'  => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $start_date = $validated['start_date'] ?? null;
        $end_date   = $validated['end_date'] ?? null;

        $stationIds = $this->resolveStationIds($request);
        $station    = count($stationIds) === 1 ? Station::find($stationIds[0]) : null;

        $employee = null;
        if (!empty($validated['employee_id'])) {
            $employee = Employee::whereIn('station_id', $stationIds)->find($validated['employee_id']);
            if (!$employee) {
                abort(403, 'ليس لديك صلاحية الوصول لهذا الموظف');
            }
        }

        // تُحفظ مع روابط الترقيم حتى لا تضيع الفلاتر عند الانتقال لصفحة أخرى
        $appends = $request->except(['_token', 'page']);

        // 1) قراءات العدادات
        $readingsQuery = MachineDetail::with(['station', 'machine', 'gun', 'employee'])
            ->whereIn('station_id', $stationIds)
            ->when($employee?->id, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($start_date, fn ($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn ($q) => $q->whereDate('date', '<=', $end_date));

        // المبيعات لا تشمل القراءات المعلقة (NULL عادية != 'pending')
        $readingsTotal = (float) (clone $readingsQuery)
            ->where(function ($q) {
                $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending');
            })
            ->sum('total');

        $readings = (clone $readingsQuery)
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(50)
            ->appends($appends);

        $pendingReadingsCount = (clone $readingsQuery)
            ->where('approval_status', 'pending')
            ->count();

        // 2) المصروفات — إجمالي فقط، بند على مستوى المحطة، بلا فلترة موظف
        $expensesTotal = (float) ExpenseDetail::whereIn('station_id', $stationIds)
            ->when($start_date, fn ($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn ($q) => $q->whereDate('date', '<=', $end_date))
            ->sum('expense_amount');

        // 3) التوريدات — إجمالي فقط
        $depositsQuery = Deposit::whereIn('station_id', $stationIds)
            ->when($employee?->id, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($start_date, fn ($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn ($q) => $q->whereDate('date', '<=', $end_date));

        $depositsTotal = (float) DepositDetail::whereIn('deposit_id', (clone $depositsQuery)->pluck('id'))
            ->sum('deposit_amount');

        // المتبقي يُؤخذ من آخر توريد داخل الفترة (الأحدث تاريخًا ثم الأحدث إدخالًا)
        $lastDeposit = (clone $depositsQuery)
            ->withSum('deposit_details as deposited_sum', 'deposit_amount')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        // 4) رصيد الأبار — الرصيد الحالي للمحطات المختارة (لا يُقيَّد بالتاريخ)
        $stocks = Stock::whereIn('station_id', $stationIds)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $stocksByType = $stocks->groupBy('type');
        $stockTotals  = [
            1 => (float) $stocks->where('type', 1)->sum('qty'),
            2 => (float) $stocks->where('type', 2)->sum('qty'),
        ];

        return view('complete_report_result', [
            'station'             => $station,
            'employee'            => $employee,
            'start_date'          => $start_date,
            'end_date'            => $end_date,
            'appends'             => $appends,
            'readings'            => $readings,
            'readingsTotal'       => $readingsTotal,
            'pendingReadingsCount'=> $pendingReadingsCount,
            'expensesTotal'       => $expensesTotal,
            'depositsTotal'       => $depositsTotal,
            'lastDeposit'         => $lastDeposit,
            'remaining'           => $lastDeposit?->remaining,
            'stocks'              => $stocks,
            'stocksByType'        => $stocksByType,
            'stockTotals'         => $stockTotals,
        ]);
    }
}
