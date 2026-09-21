<?php

namespace App\Services;

use App\Models\ExpenseDetail;
use App\Models\FuelDeliviery;
use App\Models\MachineDetail;
use App\Models\Station;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StationReportService
{
    /**
     * المحطات المحددة حسب ترتيب أسمائها — تستخدمها التقارير للعرض.
     */
    public function stationsByIds(array $stationIds): Collection
    {
        return empty($stationIds)
            ? collect()
            : Station::whereIn('id', $stationIds)->orderBy('name')->get();
    }

    /**
     * الوقود المستلم حسب النوع عبر المحطات المحددة والفترة.
     *
     * يتم الربط بأمر التوريد (fuel_orders) للحصول على نوع الوقود وسعر اللتر —
     * التسليمات غير المرتبطة بأمر لا تدخل في حساب التكلفة إطلاقًا.
     * التكلفة = كمية التسليم × سعر اللتر المقرر في أمر التوريد.
     */
    public function fuelReceivedByType(array $stationIds, ?string $startDate, ?string $endDate): Collection
    {
        return FuelDeliviery::query()
            ->join('fuel_orders', 'fuel_orders.id', '=', 'fuel_delivieries.fuel_order_id')
            ->when($stationIds, fn($q) => $q->whereIn('fuel_delivieries.station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('fuel_delivieries.date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('fuel_delivieries.date', '<=', $endDate))
            ->selectRaw('fuel_orders.fuel_type as fuel_type')
            ->selectRaw('COALESCE(SUM(fuel_delivieries.quantity), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(fuel_delivieries.quantity * fuel_orders.price_per_liter), 0) as total_cost')
            ->groupBy('fuel_orders.fuel_type')
            ->get()
            ->keyBy('fuel_type')
            ->map(fn($row) => (object) [
                'fuel_type'     => $row->fuel_type,
                'total_quantity' => (float) $row->total_quantity,
                'total_cost'    => (float) $row->total_cost,
            ]);
    }

    /**
     * تكلفة الوقود المستلم لكل محطة — نفس الربط بأمر التوريد: كمية × سعر اللتر.
     */
    public function fuelCostByStation(array $stationIds, ?string $startDate, ?string $endDate): array
    {
        return FuelDeliviery::query()
            ->join('fuel_orders', 'fuel_orders.id', '=', 'fuel_delivieries.fuel_order_id')
            ->when($stationIds, fn($q) => $q->whereIn('fuel_delivieries.station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('fuel_delivieries.date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('fuel_delivieries.date', '<=', $endDate))
            ->selectRaw('fuel_delivieries.station_id')
            ->selectRaw('COALESCE(SUM(fuel_delivieries.quantity * fuel_orders.price_per_liter), 0) as fuel_cost')
            ->groupBy('fuel_delivieries.station_id')
            ->pluck('fuel_cost', 'station_id')
            ->all();
    }

    /**
     * إجمالي المبيعات لكل محطة — يستثني قراءات العدادات المعلقة
     * (approval_status = 'pending') بنفس قاعدة MachineDetail::shiftSummary
     * حيث أن NULL يعتبر قراءة عادية معتمدة.
     */
    public function salesByStation(array $stationIds, ?string $startDate, ?string $endDate): array
    {
        return MachineDetail::query()
            ->when($stationIds, fn($q) => $q->whereIn('station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->where(fn($q) => $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending'))
            ->selectRaw('station_id')
            ->selectRaw('COALESCE(SUM(total), 0) as sales_total')
            ->groupBy('station_id')
            ->pluck('sales_total', 'station_id')
            ->all();
    }

    /**
     * إجمالي المصروفات لكل محطة — كل البنود تُحتسب عمدًا لأن المصرف
     * دُفع فعلاً بغض النظر عن حالة اعتماده (نفس قاعدة shiftSummary).
     */
    public function expensesByStation(array $stationIds, ?string $startDate, ?string $endDate): array
    {
        return ExpenseDetail::query()
            ->when($stationIds, fn($q) => $q->whereIn('station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->selectRaw('station_id')
            ->selectRaw('COALESCE(SUM(expense_amount), 0) as expenses_total')
            ->groupBy('station_id')
            ->pluck('expenses_total', 'station_id')
            ->all();
    }

    /**
     * ملخص كل محطة: المبيعات، تكلفة الوقود المستلم، المصروفات،
     * وصافي الربح = مبيعات − تكلفة الوقود − مصروفات.
     */
    public function stationSummaries(array $stationIds, ?string $startDate, ?string $endDate): Collection
    {
        $sales = $this->salesByStation($stationIds, $startDate, $endDate);
        $expenses = $this->expensesByStation($stationIds, $startDate, $endDate);
        $fuelCost = $this->fuelCostByStation($stationIds, $startDate, $endDate);

        return $this->stationsByIds($stationIds)->map(function ($station) use ($sales, $expenses, $fuelCost) {
            $salesTotal = (float) ($sales[$station->id] ?? 0);
            $expensesTotal = (float) ($expenses[$station->id] ?? 0);
            $fuelCostTotal = (float) ($fuelCost[$station->id] ?? 0);
            return (object) [
                'station_id'         => $station->id,
                'station_name'       => $station->name,
                'sales_total'        => $salesTotal,
                'cost_of_fuel_total' => $fuelCostTotal,
                'expenses_total'     => $expensesTotal,
                'net_profit'         => $salesTotal - $fuelCostTotal - $expensesTotal, // حساب الارباح
            ];
        });
    }

    public function deliveries(array $stationIds, ?string $startDate, ?string $endDate): LengthAwarePaginator
    {
        return FuelDeliviery::with('station', 'supplier', 'tuncker', 'fuelOrder')
            ->when($stationIds, fn($q) => $q->whereIn('station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(20);
    }

    public function expenseDetails(array $stationIds, ?string $startDate, ?string $endDate): LengthAwarePaginator
    {
        return ExpenseDetail::with('station')
            ->when($stationIds, fn($q) => $q->whereIn('station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(20);
    }

    public function salesDetails(array $stationIds, ?string $startDate, ?string $endDate): LengthAwarePaginator
    {
        return MachineDetail::with('station', 'machine', 'gun')
            ->when($stationIds, fn($q) => $q->whereIn('station_id', $stationIds))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->where(fn($q) => $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'pending'))
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(20);
    }
}
