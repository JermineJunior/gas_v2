@extends('layouts.app')

@section('title', 'نتائج تفاصيل المحطات')

@section('body-class', 'bg-gray-100 font-sans')

@section('no_header')
@endsection

@section('styles')
    @include('partials.theme')

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-card {
                box-shadow: none !important;
                border: none !important;
            }

            .print-inline {
                display: flex !important;
                justify-content: space-between;
                gap: 20px;
            }

            .print-inline>div {
                flex: 1;
                text-align: center;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $fuelLabels = [1 => 'جازولين', 2 => 'بنزين'];
    @endphp

    <div class="max-w-6xl mx-auto mt-10 bg-white shadow-lg rounded-xl p-6 print-card">

        <!-- شعار + العنوان -->
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="شعار المؤسسة" class="w-16 h-16 object-contain">
                <h2 class="text-2xl font-bold text-gray-700">نتائج تفاصيل المحطات</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.stations_details') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600 transition">
                    رجوع
                </a>
                <button onclick="window.print()"
                    class="bg-primary-strong text-white px-4 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    طباعة
                </button>
            </div>
        </div>

        <!-- معلومات البحث -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-[#F7ECEE] p-4 rounded-lg print-inline">
            <div>
                <span class="block text-sm text-gray-600">المحطات:</span>
                <p class="font-semibold">{{ $stations->pluck('name')->implode('، ') }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">من:</span>
                <p class="font-semibold">{{ $start_date ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">إلى:</span>
                <p class="font-semibold">{{ $end_date ?? '-' }}</p>
            </div>
        </div>

        @include('partials.station_report_summary')

        <!-- تفاصيل تسليم الوقود -->
        <h3 class="text-lg font-bold text-gray-700 mt-10 mb-2 border-t pt-6">تفاصيل تسليم الوقود</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">المحطة</th>
                        <th class="p-3 text-center">نوع الوقود</th>
                        <th class="p-3 text-center">الكمية (لتر)</th>
                        <th class="p-3 text-center">أمر التوريد</th>
                        <th class="p-3 text-center">سعر اللتر</th>
                        <th class="p-3 text-center">التكلفة</th>
                        <th class="p-3 text-center">المورد</th>
                        <th class="p-3 text-center">رقم التانكر</th>
                        <th class="p-3 text-center">السائق</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $deliveries->firstItem() + $loop->index }}</td>
                            <td class="p-3 text-center">{{ $delivery->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">{{ $delivery->station->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $delivery->fuelOrder ? ($fuelLabels[$delivery->fuelOrder->fuel_type] ?? 'غير محدد') : 'غير مرتبط' }}</td>
                            <td class="p-3 text-center">{{ formatNumber($delivery->quantity) }}</td>
                            <td class="p-3 text-center">
                                @if ($delivery->fuelOrder)
                                    #{{ $delivery->fuelOrder->id }} ({{ $delivery->fuelOrder->date->format('Y/m/d') }})
                                @else
                                    <span class="inline-block bg-amber-100 text-amber-700 border border-amber-300 rounded-full px-2.5 py-0.5 text-xs font-semibold">بدون طلبية</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">{{ $delivery->fuelOrder ? formatNumber($delivery->fuelOrder->price_per_liter) . ' جنيه' : '-' }}</td>
                            <td class="p-3 text-center">{{ $delivery->fuelOrder ? formatNumber($delivery->quantity * $delivery->fuelOrder->price_per_liter) . ' جنيه' : '-' }}</td>
                            <td class="p-3 text-center">{{ $delivery->supplier->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $delivery->tuncker->tuncker_no ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $delivery->tuncker->driver_name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $deliveries->appends($appends)->links() }}</div>

        <!-- تفاصيل المصروفات -->
        <h3 class="text-lg font-bold text-gray-700 mt-10 mb-2 border-t pt-6">تفاصيل المصروفات</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">المحطة</th>
                        <th class="p-3 text-center">المبلغ</th>
                        <th class="p-3 text-center">الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenseDetails as $expense)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $expenseDetails->firstItem() + $loop->index }}</td>
                            <td class="p-3 text-center">{{ $expense->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">{{ $expense->station->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ formatNumber($expense->expense_amount) }} جنيه</td>
                            <td class="p-3 text-center">{{ $expense->expense_desc ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $expenseDetails->appends($appends)->links() }}</div>

        <!-- تفاصيل مبيعات العدادات -->
        <h3 class="text-lg font-bold text-gray-700 mt-10 mb-2 border-t pt-6">تفاصيل مبيعات العدادات</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">المحطة</th>
                        <th class="p-3 text-center">الماكينة</th>
                        <th class="p-3 text-center">المسدس</th>
                        <th class="p-3 text-center">الصافي</th>
                        <th class="p-3 text-center">السعر</th>
                        <th class="p-3 text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesDetails as $sale)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $salesDetails->firstItem() + $loop->index }}</td>
                            <td class="p-3 text-center">{{ $sale->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">{{ $sale->station->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $sale->machine->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $sale->gun->name ?? '-' }}</td>
                            <td class="p-3 text-center">{{ formatNumber($sale->net) }} لتر</td>
                            <td class="p-3 text-center">{{ formatNumber($sale->price) }} جنيه</td>
                            <td class="p-3 text-center">{{ formatNumber($sale->total) }} جنيه</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $salesDetails->appends($appends)->links() }}</div>
    </div>
@endsection