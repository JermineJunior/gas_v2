@php
    $fuelLabels = [1 => 'جازولين', 2 => 'بنزين'];
    $fuelTotal = $fuelByType->sum('total_quantity');
    $fuelTotalCost = $fuelByType->sum('total_cost');
    $summaryTotalSales = $stationSummaries->sum('sales_total');
    $summaryTotalCost = $stationSummaries->sum('cost_of_fuel_total');
    $summaryTotalExpenses = $stationSummaries->sum('expenses_total');
    $summaryTotalNet = $summaryTotalSales - $summaryTotalCost - $summaryTotalExpenses;
@endphp

<!-- الوقود المستلم حسب النوع -->
<h3 class="text-lg font-bold text-gray-700 mb-2">الوقود المستلم حسب النوع</h3>
<div class="overflow-x-auto mb-8">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
        <thead>
            <tr class="bg-primary-strong text-white text-sm">
                <th class="p-3 text-center">#</th>
                <th class="p-3 text-center">نوع الوقود</th>
                <th class="p-3 text-center">الكمية (لتر)</th>
                <th class="p-3 text-center">التكلفة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fuelByType as $fuelType => $row)
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                    <td class="p-3 text-center">{{ $loop->iteration }}</td>
                    <td class="p-3 text-center">{{ $fuelLabels[$fuelType] ?? 'غير محدد' }}</td>
                    <td class="p-3 text-center">{{ formatNumber($row->total_quantity) }}</td>
                    <td class="p-3 text-center">{{ formatNumber($row->total_cost) }} جنيه</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                </tr>
            @endforelse
        </tbody>
        @if ($fuelByType->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="2" class="p-3 text-center font-semibold">الإجمالي</td>
                    <td class="p-3 text-center font-bold text-primary-strong">{{ formatNumber($fuelTotal) }} لتر</td>
                    <td class="p-3 text-center font-bold text-primary-strong">{{ formatNumber($fuelTotalCost) }} جنيه</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<!-- المبيعات وتكلفة الوقود والمصروفات وصافي الربح لكل محطة -->
<h3 class="text-lg font-bold text-gray-700 mb-2">المبيعات وتكلفة الوقود والمصروفات وصافي الربح لكل محطة</h3>
<div class="overflow-x-auto mb-4">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
        <thead>
            <tr class="bg-primary-strong text-white text-sm">
                <th class="p-3 text-center">#</th>
                <th class="p-3 text-center">المحطة</th>
                <th class="p-3 text-center">إجمالي المبيعات</th>
                <th class="p-3 text-center">تكلفة الوقود</th>
                <th class="p-3 text-center">إجمالي المصروفات</th>
                <th class="p-3 text-center">صافي الربح</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stationSummaries as $summary)
                @php $rowClass = $summary->net_profit < 0 ? 'text-red-600' : 'font-semibold'; @endphp
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                    <td class="p-3 text-center">{{ $loop->iteration }}</td>
                    <td class="p-3 text-center">{{ $summary->station_name }}</td>
                    <td class="p-3 text-center">{{ formatNumber($summary->sales_total) }} جنيه</td>
                    <td class="p-3 text-center">{{ formatNumber($summary->cost_of_fuel_total) }} جنيه</td>
                    <td class="p-3 text-center">{{ formatNumber($summary->expenses_total) }} جنيه</td>
                    <td class="p-3 text-center {{ $rowClass }}">{{ formatNumber($summary->net_profit) }} جنيه</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">لا توجد محطات محددة</td>
                </tr>
            @endforelse
        </tbody>
        @if ($stationSummaries->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="2" class="p-3 text-center font-semibold">الإجمالي</td>
                    <td class="p-3 text-center font-bold text-primary-strong">{{ formatNumber($summaryTotalSales) }} جنيه</td>
                    <td class="p-3 text-center font-bold text-primary-strong">{{ formatNumber($summaryTotalCost) }} جنيه</td>
                    <td class="p-3 text-center font-bold text-primary-strong">{{ formatNumber($summaryTotalExpenses) }} جنيه</td>
                    <td class="p-3 text-center font-bold text-primary-strong {{ $summaryTotalNet < 0 ? 'text-red-600' : '' }}">{{ formatNumber($summaryTotalNet) }} جنيه</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>