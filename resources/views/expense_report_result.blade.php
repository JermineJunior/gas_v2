@extends('layouts.app')

@section('title', 'نتائج تقرير المصروفات')

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
                <h2 class="text-2xl font-bold text-gray-700">نتائج تقرير المصروفات</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.expense_list') }}"
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-[#F7ECEE] p-4 rounded-lg print-inline">
            <div>
                <span class="block text-sm text-gray-600">من:</span>
                <p class="font-semibold">{{ $startDate ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">إلى:</span>
                <p class="font-semibold">{{ $endDate ?? '-' }}</p>
            </div>
        </div>

        <!-- جدول النتائج -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">البيان</th>
                        <th class="p-3 text-center">المستخدم</th>
                        <th class="p-3 text-center">المبلغ</th>
                        <th class="p-3 text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $detail)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $loop->iteration }}</td>
                            <td class="p-3 text-center">{{ $detail->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">{{ $detail->expense_desc ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $detail->expense->user->name ?? '-' }}</td>
                            <td class="p-3 text-center font-semibold">{{ formatNumber($detail->expense_amount) }}</td>
                            <td class="p-3 text-center">
                                @if($detail->status == 1)
                                    <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold"
                                        title="{{ $detail->approver?->name ?? '' }} — {{ $detail->approved_at?->format('Y/m/d') }}">معتمد</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-500 text-xs font-semibold">قيد الاعتماد</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- الملخص -->
        @if ($details->count() > 0)
            <div class="mt-6 bg-primary-soft rounded-lg p-4 flex items-center justify-between font-bold text-gray-800 flex-wrap gap-4">
                <span class="text-lg">الملخص النهائي</span>
                <div class="flex items-center gap-6 flex-wrap">
                    <span class="text-green-700">المعتمد: {{ formatNumber($totalApproved) }}</span>
                    <span class="text-gray-500">قيد الاعتماد: {{ formatNumber($totalPending) }}</span>
                    <span class="text-blue-700">الإجمالي الكلي: {{ formatNumber($total) }}</span>
                </div>
            </div>
        @endif
    </div>
@endsection
