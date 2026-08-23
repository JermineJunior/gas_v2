@extends('layouts.app')

@section('title', 'نتيجة تقرير العدادات بالماكينات')

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
                <h2 class="text-2xl font-bold text-gray-700">تقرير العدادات بالماكينات</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.machine_report') }}"
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
        @if($station)
            <div class="grid grid-cols-1 gap-4 mb-6 bg-[#F7ECEE] p-4 rounded-lg print-inline">
                <div>
                    <span class="block text-sm text-gray-600">المحطة:</span>
                    <p class="font-semibold">{{ $station->name }}</p>
                </div>
            </div>
        @endif

        @if($results->isEmpty())
            <p class="text-gray-500 text-center py-8">لا توجد بيانات</p>
        @else
            @php $grandNet = 0; $grandAmount = 0; @endphp
            @foreach($results as $row)
                @php $grandNet += $row['total_net']; $grandAmount += $row['total_amount']; @endphp
                <div class="mb-6 border rounded-lg overflow-hidden">
                    {{-- Machine header --}}
                    <div class="bg-primary-soft px-4 py-3 flex items-center justify-between">
                        <span class="font-bold text-lg text-gray-800">{{ $row['machine']->name }}</span>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-green-700 font-semibold">السحوبات: {{ formatNumber($row['total_net']) }} لتر</span>
                            <span class="text-blue-700 font-semibold">المبلغ: {{ formatNumber($row['total_amount']) }}</span>
                        </div>
                    </div>

                    {{-- Guns table --}}
                    @if($row['guns']->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-primary-strong text-white">
                                        <th class="p-3 text-center">#</th>
                                        <th class="p-3 text-center">المسدس</th>
                                        <th class="p-3 text-center">الصافي (لتر)</th>
                                        <th class="p-3 text-center">المبلغ</th>
                                        {{-- <th class="p-3 text-center">عدد</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($row['guns'] as $i => $gun)
                                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                            <td class="p-3 text-center">{{ $i + 1 }}</td>
                                            <td class="p-3 text-center font-semibold">{{ $gun['gun_name'] }}</td>
                                            <td class="p-3 text-center">{{ formatNumber($gun['total_net']) }}</td>
                                            <td class="p-3 text-center">{{ formatNumber($gun['total_amount']) }}</td>
                                            {{-- <td class="p-3 text-center">{{ $gun['count'] }}</td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="p-3 text-sm text-gray-500">لا توجد سحوبات</p>
                    @endif

                    {{-- Stock info --}}
                    <div class="bg-green-50 border-t px-4 py-2 flex items-center gap-6 text-sm">
                        <span class="font-semibold text-gray-700">البير: <b class="text-green-800">{{ $row['stock_name'] }}</b></span>
                        <span class="text-gray-600">النوع: <b>{{ $row['stock_type'] }}</b></span>
                        <span class="text-gray-600">الرصيد المتبقي: <b class="text-amber-700 font-bold">{{ formatNumber($row['stock_remaining']) }}</b> لتر</span>
                    </div>
                </div>
            @endforeach

            {{-- Grand total --}}
            <div class="bg-primary-soft rounded-lg p-4 flex items-center justify-between font-bold text-gray-800">
                <span class="text-lg">الإجمالي الكلي</span>
                <div class="flex items-center gap-6">
                    <span class="text-green-700">{{ formatNumber($grandNet) }} لتر</span>
                    <span class="text-blue-700">{{ formatNumber($grandAmount) }}</span>
                </div>
            </div>
        @endif
    </div>
@endsection
