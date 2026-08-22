@extends('layouts.app')

@section('title', 'ملخص مخزون المستودعات')

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
                <h2 class="text-2xl font-bold text-gray-700">ملخص مخزون المستودعات</h2>
            </div>
            <div class="no-print flex gap-3">
                <button onclick="window.print()"
                    class="bg-primary-strong text-white px-4 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    طباعة
                </button>
            </div>
        </div>

        <!-- جدول المخزون -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">المستودع</th>
                        <th class="p-3 text-center">جازولين (لتر)</th>
                        <th class="p-3 text-center">بنزين (لتر)</th>
                        <th class="p-3 text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $i => $warehouse)
                        @php
                            $gasoline = $warehouse->stocks->where('fuel_type', 1)->sum('current_stock');
                            $benzine = $warehouse->stocks->where('fuel_type', 2)->sum('current_stock');
                        @endphp
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $i + 1 }}</td>
                            <td class="p-3 text-center font-semibold">{{ $warehouse->name }}</td>
                            <td class="p-3 text-center text-green-700 font-semibold">{{ formatNumber($gasoline) }}</td>
                            <td class="p-3 text-center text-sky-700 font-semibold">{{ formatNumber($benzine) }}</td>
                            <td class="p-3 text-center text-amber-700 font-bold">{{ formatNumber($gasoline + $benzine) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">لا توجد مستودعات</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($warehouses->isNotEmpty())
                    <tfoot>
                        <tr class="bg-primary-soft font-bold text-gray-800">
                            <td colspan="2" class="p-3 text-center">الإجمالي الكلي</td>
                            <td class="p-3 text-center text-green-700">{{ formatNumber($grandGasoline) }}</td>
                            <td class="p-3 text-center text-sky-700">{{ formatNumber($grandBenzine) }}</td>
                            <td class="p-3 text-center text-amber-700">{{ formatNumber($grandGasoline + $grandBenzine) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
