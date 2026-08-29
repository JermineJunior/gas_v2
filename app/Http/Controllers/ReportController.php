<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DepositDetail;
use App\Models\Detail;
use App\Models\FuelDeliviery;
use App\Models\FuelOrder;
use App\Models\MachineDetail;
use App\Models\Station;
use App\Models\Supplier;
use App\Models\Deposit;
use App\Models\Tuncker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function deposit_detail()
    {
        $stations = Auth::user()->stations;
        return view('deposit_detail', compact('stations'));
    }

    public function deposit_detail_result(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = DepositDetail::with('deposit.employee')
            ->when($request->station_id, function ($query) use ($request) {
                return $query->where('station_id', $request->station_id);
            })
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
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = MachineDetail::when($request->station_id, function ($query) use ($request) {
            return $query->where('station_id', $request->station_id);
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
        $request->merge([
            'station_id' => Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id,
        ]);
        $station = Station::find($request->station_id) ?? null;
        $operations = Tuncker::when($request->station_id, function ($query) use ($request) {
            return $query->where('station_id', $request->station_id);
        })
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

        $deliveries = FuelDeliviery::with('supplier', 'station')
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

        return view('supplier_result', compact('orders', 'deliveries', 'total_requested', 'total_delivered', 'start_date', 'end_date', 'supplier'));
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
        $stationId = $request->station_id;
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        if ($stationId) $machinesQuery->where('station_id', $stationId);
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

        $station = $stationId ? Station::find($stationId) : null;
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
            'details', 'total', 'totalApproved', 'totalPending', 'startDate', 'endDate'
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
            'byMonth', 'grandTotal', 'station', 'startDate', 'endDate'
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
        $stationId =  $request->station_id;
        $machineId = $request->machine_id;
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $machinesQuery = \App\Models\Machine::query()->with('stock.station');
        if ($stationId) $machinesQuery->where('station_id', $stationId);
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

        $station = $stationId ? Station::find($stationId) : null;
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
        $stationId = Auth::user()->type == 3 ? Auth::user()->stations[0]->pivot->station_id : $request->station_id;
        $fuelType = $request->fuel_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $stocksQuery = \App\Models\Stock::query()->with('station');
        if ($stationId) $stocksQuery->where('station_id', $stationId);
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

        $station = $stationId ? Station::find($stationId) : null;
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
}
