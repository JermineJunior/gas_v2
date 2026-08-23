@extends('layouts.app')

@section('title', 'نتائج ملخص المصروفات')

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
                <h2 class="text-2xl font-bold text-gray-700">ملخص المصروفات — {{ $station->name }}</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.expense_summary') }}"
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
                <p class="font-semibold">{{ $startDate ?? 'من البداية' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">إلى:</span>
                <p class="font-semibold">{{ $endDate ?? 'حتى الآن' }}</p>
            </div>
        </div>

        <!-- حسب الشهر -->
        <div class="mb-8 border rounded-lg overflow-hidden">
            <div class="bg-primary-soft px-4 py-3">
                <span class="font-bold text-lg text-gray-800">المصروفات حسب الشهر</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-primary-strong text-white">
                            <th class="p-3 text-center">الشهر</th>
                            <th class="p-3 text-center">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byMonth as $row)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center font-semibold">{{ $row->month }}</td>
                                <td class="p-3 text-center font-bold text-blue-700">{{ formatNumber($row->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-4 text-center text-gray-500">لا توجد بيانات في الفترة المحددة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الملخص -->
        @if ($byMonth->isNotEmpty())
            <div class="mt-6 bg-primary-soft rounded-lg p-4 flex justify-between items-center">
                <span class="text-lg font-bold text-gray-700">الإجمالي الكلي للمصروفات:</span>
                <span class="text-xl font-bold text-blue-700">{{ formatNumber($grandTotal) }} ج.س</span>
            </div>
        @endif
    </div>
@endsection
