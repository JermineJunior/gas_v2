@extends('layouts.app')

@section('title', 'نتائج تقرير حساب الموظف')

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
                <h2 class="text-2xl font-bold text-gray-700">تقرير حساب الموظف — {{ $employee->name }}</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.employee_account') }}"
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
                <span class="block text-sm text-gray-600">المحطة:</span>
                <p class="font-semibold">{{ $station->name }}</p>
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

        <!-- العدادات (نفس منطق شاشة التوريدات) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-primary-soft border border-primary-strong/30 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 mb-1">المطلوب من العداد القديم</p>
                <p class="text-xl font-bold text-gray-800">{{ formatNumber($totalOldMachine) }} ج.س</p>
            </div>
            <div class="bg-primary-soft border border-primary-strong/30 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 mb-1">المطلوب من العداد الجديد</p>
                <p class="text-xl font-bold text-gray-800">{{ formatNumber($totalNewMachine) }} ج.س</p>
            </div>
        </div>

        <!-- جدول التوريدات -->
        <div class="mb-8 border rounded-lg overflow-hidden">
            <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                <span class="font-bold text-lg text-gray-800">توريدات الموظف</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-primary-strong text-white">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">التاريخ</th>
                            <th class="p-3 text-center">البيان</th>
                            <th class="p-3 text-center">المبلغ</th>
                            <th class="p-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                <td class="p-3 text-center">{{ $deposit->date->format('Y/m/d') }}</td>
                                <td class="p-3 text-center">{{ $deposit->deposit_desc ?? '-' }}</td>
                                <td class="p-3 text-center font-semibold">{{ formatNumber($deposit->deposit_amount) }}</td>
                                <td class="p-3 text-center">
                                    @if($deposit->status == 1)
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold"
                                            title="{{ $deposit->approver?->name ?? '' }} — {{ $deposit->approved_at?->format('Y/m/d') }}">معتمد</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-500 text-xs font-semibold">قيد الاعتماد</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">لا توجد توريدات في الفترة المحددة</td>
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
                <span class="text-blue-700">العداد القديم: {{ formatNumber($totalOldMachine) }}</span>
                <span class="text-blue-700">العداد الجديد: {{ formatNumber($totalNewMachine) }}</span>
                <span class="text-green-700">إجمالي التوريدات: {{ formatNumber($totalDeposits) }}</span>
                <span class="text-amber-700">المتبقي: {{ formatNumber($remaining) }}</span>
            </div>
        </div>
    </div>
@endsection
