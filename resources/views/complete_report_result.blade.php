@extends('layouts.app')

@section('title', 'نتائج التقرير المتكامل')

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

            /* عند الطباعة نخلي البيانات في سطر واحد */
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
    <div class="max-w-6xl mx-auto mt-10 bg-white shadow-lg rounded-xl p-6 print-card">

        <!-- شعار + العنوان -->
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="شعار المؤسسة" class="w-16 h-16 object-contain">
                <h2 class="text-2xl font-bold text-gray-700">نتائج التقرير المتكامل</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.complete') }}"
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-[#F7ECEE] p-4 rounded-lg print-inline">
            <div>
                <span class="block text-sm text-gray-600">المحطة:</span>
                <p class="font-semibold">{{ $station->name ?? 'كل المحطات' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">الموظف:</span>
                <p class="font-semibold">{{ $employee->name ?? 'كل الموظفين' }}</p>
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

        <!-- ملخص علوي -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                <span class="block text-sm text-gray-600">إجمالي المبيعات (بدون المعلقة)</span>
                <p class="text-xl font-bold text-primary-strong mt-1">{{ formatNumber($readingsTotal) }} جنيه</p>
                @if ($pendingReadingsCount > 0)
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                        {{ $pendingReadingsCount }} قراءة بانتظار الموافقة غير محتسبة
                    </span>
                @endif
            </div>
            <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                <span class="block text-sm text-gray-600">إجمالي المصروفات</span>
                <p class="text-xl font-bold text-primary-strong mt-1">{{ formatNumber($expensesTotal) }} جنيه</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                <span class="block text-sm text-gray-600">إجمالي التوريدات</span>
                <p class="text-xl font-bold text-primary-strong mt-1">{{ formatNumber($depositsTotal) }} جنيه</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                <span class="block text-sm text-gray-600">المتبقي (آخر توريد في الفترة)</span>
                <p class="text-xl font-bold text-primary-strong mt-1">
                    {{ $lastDeposit ? formatNumber($remaining) : '-' }}
                    @if ($lastDeposit) جنيه @endif
                </p>
            </div>
        </div>

        <!-- رصيد الأبر الحالي -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">رصيد الأبر (الرصيد الحالي)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @foreach ([1 => 'جازولين', 2 => 'بنزين'] as $type => $typeLabel)
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <span class="block text-sm text-gray-600">إجمالي {{ $typeLabel }}</span>
                        <p class="text-xl font-bold text-primary-strong mt-1">{{ formatNumber($stockTotals[$type] ?? 0) }} لتر</p>
                    </div>
                @endforeach
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                    <thead>
                        <tr class="bg-primary-strong text-white text-sm">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">النوع</th>
                            @if (!$station)
                                <th class="p-3 text-center">المحطة</th>
                            @endif
                            <th class="p-3 text-center">البير</th>
                            <th class="p-3 text-center">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stocks as $stock)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                <td class="p-3 text-center">{{ $stock->type == 1 ? 'جازولين' : 'بنزين' }}</td>
                                @if (!$station)
                                    <td class="p-3 text-center">{{ $stock->station->name ?? '-' }}</td>
                                @endif
                                <td class="p-3 text-center">{{ $stock->name }}</td>
                                <td class="p-3 text-center">{{ formatNumber($stock->qty) }} لتر</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">لا توجد أبار مسجلة لهذه المحطة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- قراءات العدادات -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-3">قراءات العدادات</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                    <thead>
                        <tr class="bg-primary-strong text-white text-sm">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">التاريخ</th>
                            @if (!$station)
                                <th class="p-3 text-center">المحطة</th>
                            @endif
                            <th class="p-3 text-center">الموظف</th>
                            <th class="p-3 text-center">الماكينة</th>
                            <th class="p-3 text-center">المسدس</th>
                            <th class="p-3 text-center">عداد البداية</th>
                            <th class="p-3 text-center">عداد النهاية</th>
                            <th class="p-3 text-center">الصافي</th>
                            <th class="p-3 text-center">السعر</th>
                            <th class="p-3 text-center">اجمالي المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($readings as $reading)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $readings->firstItem() + $loop->index }}</td>
                                <td class="p-3 text-center">{{ $reading->date->format('Y/m/d') }}</td>
                                @if (!$station)
                                    <td class="p-3 text-center">{{ $reading->station->name ?? '-' }}</td>
                                @endif
                                <td class="p-3 text-center">{{ $reading->employee->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ $reading->machine->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ $reading->gun->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ formatNumber($reading->start_counter) }} لتر</td>
                                <td class="p-3 text-center">{{ formatNumber($reading->end_counter) }} لتر</td>
                                <td class="p-3 text-center">
                                    {{ formatNumber($reading->net) }} لتر
                                    @if ($reading->approval_status === 'pending')
                                        <span class="mr-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold"
                                            title="بانتظار موافقة المدير — لم تُحتسب من إجمالي المبيعات">بانتظار الموافقة</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">{{ formatNumber($reading->price) }} جنيه</td>
                                <td class="p-3 text-center">{{ formatNumber($reading->total) }} جنيه</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="p-4 text-center text-gray-500">لا توجد قراءات لعرضها</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($readings->count() > 0)
                        <tfoot>
                            <tr class="bg-gray-100">
                                <td class="p-3 text-center" colspan="{{ $station ? 6 : 7 }}">إجمالي المبيعات (بدون المعلقة)</td>
                                <td class="p-3 text-center"></td>
                                <td class="p-3 text-center"></td>
                                <td class="p-3 text-center"></td>
                                <td class="p-3 text-center">{{ formatNumber($readingsTotal) }} جنيه</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="mt-4 no-print">{{ $readings->appends($appends)->links() }}</div>
        </div>

    </div>
@endsection
