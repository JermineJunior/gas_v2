@extends('layouts.app')

@section('title', 'نتائج تقرير استهلاك المستودعات')

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
                <h2 class="text-2xl font-bold text-gray-700">تقرير استهلاك المستودعات</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('warehouse_reports.consumption') }}"
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

        <!-- الاستهلاك حسب نوع الوقود -->
        <div class="mb-8 border rounded-lg overflow-hidden">
            <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                <span class="font-bold text-lg text-gray-800">الاستهلاك حسب نوع الوقود</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-primary-strong text-white">
                            <th class="p-3 text-center">نوع الوقود</th>
                            <th class="p-3 text-center">إجمالي المسحوبات (لتر)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byFuelType as $fuelType => $total)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $fuelType == 1 ? 'bg-green-100 text-green-700' : 'bg-sky-100 text-sky-700' }}">
                                        {{ $fuelType == 1 ? 'جازولين' : 'بنزين' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-bold {{ $fuelType == 1 ? 'text-green-700' : 'text-sky-700' }}">{{ formatNumber($total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-4 text-center text-gray-500">لا توجد بيانات في الفترة المحددة</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($byFuelType->isNotEmpty())
                        <tfoot>
                            <tr class="bg-primary-soft font-bold text-gray-800">
                                <td class="p-3 text-center">الإجمالي الكلي</td>
                                <td class="p-3 text-center text-amber-700">{{ formatNumber($grandTotal) }} لتر</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- الاستهلاك حسب المستودع -->
        @if ($byWarehouse->isNotEmpty())
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                    <span class="font-bold text-lg text-gray-800">الاستهلاك حسب المستودع</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-primary-strong text-white">
                                <th class="p-3 text-center">#</th>
                                <th class="p-3 text-center">المستودع</th>
                                <th class="p-3 text-center">جازولين (لتر)</th>
                                <th class="p-3 text-center">بنزين (لتر)</th>
                                <th class="p-3 text-center">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byWarehouse as $warehouseId => $rows)
                                @php
                                    $warehouse = $warehouses->get($warehouseId);
                                    $gasoline = $rows->where('fuel_type', 1)->sum('total');
                                    $benzine = $rows->where('fuel_type', 2)->sum('total');
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                    <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                    <td class="p-3 text-center font-semibold">{{ $warehouse?->name ?? '-' }}</td>
                                    <td class="p-3 text-center text-green-700 font-semibold">{{ formatNumber($gasoline) }}</td>
                                    <td class="p-3 text-center text-sky-700 font-semibold">{{ formatNumber($benzine) }}</td>
                                    <td class="p-3 text-center text-amber-700 font-bold">{{ formatNumber($gasoline + $benzine) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
