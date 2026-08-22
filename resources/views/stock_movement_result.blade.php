@extends('layouts.app')

@section('title', 'نتائج تقرير حركة البير')

@section('body-class', 'bg-gray-100 font-sans')

@section('no_header')
@endsection

@section('styles')
    @include('partials.theme')

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: none !important; }
            .print-inline { display: flex !important; justify-content: space-between; gap: 20px; }
            .print-inline > div { flex: 1; text-align: center; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto mt-10 bg-white shadow-lg rounded-xl p-6 print-card">

        <!-- شعار + العنوان -->
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="شعار المؤسسة" class="w-16 h-16 object-contain">
                <h2 class="text-2xl font-bold text-gray-700">تقرير حركة البير - {{ $stock->name }}</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.stock_movement') }}"
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
                <p class="font-semibold">{{ $stock->station->name ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">النوع:</span>
                <p class="font-semibold">{{ $stock->type == 1 ? 'جازولين' : 'بنزين' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">من:</span>
                <p class="font-semibold">{{ $startDate ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">إلى:</span>
                <p class="font-semibold">{{ $endDate ?? '-' }}</p>
            </div>
        </div>

        <!-- الاضافات -->
        <div class="mb-8 border rounded-lg overflow-hidden">
            <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                <span class="font-bold text-lg text-gray-800">الاضافات (تفريغ التناكر)</span>
                <span class="text-green-700 font-semibold">الاجمالي: {{ formatNumber($totalAdditions) }} لتر</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-primary-strong text-white">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">اسم السائق</th>
                            <th class="p-3 text-center">رقم التنكر</th>
                            <th class="p-3 text-center">المورد</th>
                            <th class="p-3 text-center">الكمية (لتر)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($additions as $i => $add)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $i + 1 }}</td>
                                <td class="p-3 text-center">{{ $add['driver_name'] }}</td>
                                <td class="p-3 text-center">{{ $add['tuncker_no'] }}</td>
                                <td class="p-3 text-center">{{ $add['supplier'] }}</td>
                                <td class="p-3 text-center text-green-700 font-semibold">{{ formatNumber($add['qty']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">لا توجد اضافات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- المسحوبات -->
        <div class="mb-8 border rounded-lg overflow-hidden">
            <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                <span class="font-bold text-lg text-gray-800">المسحوبات (قراءات الماكينات)</span>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-red-700 font-semibold">الصافي: {{ formatNumber($totalWithdrawals) }} لتر</span>
                    <span class="text-blue-700 font-semibold">المبلغ: {{ formatNumber($totalWithdrawAmount) }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-primary-strong text-white">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">التاريخ</th>
                            <th class="p-3 text-center">الماكينة</th>
                            <th class="p-3 text-center">المسدس</th>
                            <th class="p-3 text-center">عداد البداية</th>
                            <th class="p-3 text-center">عداد النهاية</th>
                            <th class="p-3 text-center">الصافي (لتر)</th>
                            <th class="p-3 text-center">السعر</th>
                            <th class="p-3 text-center">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $i => $wd)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $i + 1 }}</td>
                                <td class="p-3 text-center">{{ $wd->date->format('Y/m/d') }}</td>
                                <td class="p-3 text-center">{{ $wd->machine->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ $wd->gun->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ formatNumber($wd->start_counter) }}</td>
                                <td class="p-3 text-center">{{ formatNumber($wd->end_counter) }}</td>
                                <td class="p-3 text-center text-red-700 font-semibold">{{ formatNumber($wd->net) }}</td>
                                <td class="p-3 text-center">{{ formatNumber($wd->price) }}</td>
                                <td class="p-3 text-center">{{ formatNumber($wd->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-4 text-center text-gray-500">لا توجد مسحوبات في الفترة المحددة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الملخص -->
        <div class="bg-primary-soft rounded-lg p-4 flex items-center justify-between font-bold text-gray-800 flex-wrap gap-4">
            <span class="text-lg">الملخص النهائي</span>
            <div class="flex items-center gap-6 flex-wrap">
                <span class="text-green-700">الاضافات: {{ formatNumber($totalAdditions) }} لتر</span>
                <span class="text-red-700">المسحوبات: {{ formatNumber($totalWithdrawals) }} لتر</span>
                <span class="text-blue-700">مبلغ المسحوبات: {{ formatNumber($totalWithdrawAmount) }}</span>
                <span class="text-amber-700">الرصيد الحالي: {{ formatNumber($stock->qty) }} لتر</span>
            </div>
        </div>
    </div>
@endsection
