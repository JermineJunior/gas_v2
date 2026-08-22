@extends('layouts.app')

@section('title', 'نتائج تقرير اضافات المستودعات')

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
                <h2 class="text-2xl font-bold text-gray-700">نتائج تقرير اضافات المستودعات</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('warehouse_reports.additions') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600 transition">
                    رجوع
                </a>
                <button onclick="window.print()"
                    class="bg-primary-strong text-white px-4 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    طباعة
                </button>
            </div>
        </div>

        <!-- جدول النتائج -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">المستودع</th>
                        <th class="p-3 text-center">نوع الوقود</th>
                        <th class="p-3 text-center">المصدر</th>
                        <th class="p-3 text-center">الكمية (لتر)</th>
                        <th class="p-3 text-center">ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $loop->iteration }}</td>
                            <td class="p-3 text-center">{{ $t->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">{{ $t->warehouse->name ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $t->fuel_type == 1 ? 'bg-green-100 text-green-700' : 'bg-sky-100 text-sky-700' }}">
                                    {{ $t->fuel_type == 1 ? 'جازولين' : 'بنزين' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">{{ $t->source ?? '-' }}</td>
                            <td class="p-3 text-center text-green-700 font-semibold">{{ formatNumber($t->quantity) }}</td>
                            <td class="p-3 text-center">{{ $t->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ملخص -->
        @if ($transactions->count() > 0)
            <div class="mt-6 bg-primary-soft rounded-lg p-4 flex justify-between items-center">
                <span class="text-lg font-bold text-gray-700">إجمالي الاضافات:</span>
                <span class="text-xl font-bold text-green-700">{{ formatNumber($total) }} لتر</span>
            </div>
        @endif
    </div>
@endsection
