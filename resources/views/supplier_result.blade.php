@extends('layouts.app')

@section('title', 'نتائج تقرير الموردين')

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
                <h2 class="text-2xl font-bold text-gray-700">نتائج تقرير الموردين</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.supplier') }}"
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
                <span class="block text-sm text-gray-600">المورد:</span>
                <p class="font-semibold">{{ $supplier->name ?? 'كل الموردين' }}</p>
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

        <!-- طلبات الوقود (الكمية المطلوبة) -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-3">طلبات الوقود (الكمية المطلوبة)</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                    <thead>
                        <tr class="bg-primary-strong text-white text-sm">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">التاريخ</th>
                            @if (!$supplier)
                                <th class="p-3 text-center">المورد</th>
                            @endif
                            <th class="p-3 text-center">الكمية المطلوبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                <td class="p-3 text-center">{{ $order->date->format('Y/m/d') }}</td>

                                @if (!$supplier)
                                    <td class="p-3 text-center">{{ $order->supplier->name ?? '-' }}</td>
                                @endif
                                <td class="p-3 text-center">{{ formatNumber($order->quantity) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">لا توجد طلبات لعرضها</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#F7ECEE] font-bold">
                            <td colspan="{{ $supplier ? 2 : 3 }}" class="p-3 text-center">إجمالي الطلبات</td>
                            <td class="p-3 text-center text-blue-700">{{ formatNumber($total_requested) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- توريدات الوقود (الكمية الموردة للمحطات) -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-3">توريدات الوقود (الكمية الموردة للمحطات)</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                    <thead>
                        <tr class="bg-primary-strong text-white text-sm">
                            <th class="p-3 text-center">#</th>
                            <th class="p-3 text-center">التاريخ</th>
                            @if (!$supplier)
                                <th class="p-3 text-center">المورد</th>
                            @endif
                            <th class="p-3 text-center">المحطة</th>
                            <th class="p-3 text-center">الكمية الموردة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                                <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                <td class="p-3 text-center">{{ $delivery->date->format('Y/m/d') }}</td>

                                @if (!$supplier)
                                    <td class="p-3 text-center">{{ $delivery->supplier->name ?? '-' }}</td>
                                @endif
                                <td class="p-3 text-center">{{ $delivery->station->name ?? '-' }}</td>
                                <td class="p-3 text-center">{{ formatNumber($delivery->quantity) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">لا توجد توريدات لعرضها</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#F7ECEE] font-bold">
                            <td colspan="{{ $supplier ? 3 : 4 }}" class="p-3 text-center">إجمالي التوريدات</td>
                            <td class="p-3 text-center text-green-700">{{ formatNumber($total_delivered) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ملخص المطلوب مقابل المورد -->
        <div class="mt-6 bg-[#F7ECEE] rounded-lg p-4 flex flex-wrap items-center justify-center gap-6 text-md font-semibold print-inline">
            <span class="text-blue-700">إجمالي المطلوب: <b>{{ formatNumber($total_requested) }}</b></span>
            <span class="text-green-700">إجمالي المورد: <b>{{ formatNumber($total_delivered) }}</b></span>
            <span class="text-gray-700">الفرق: <b>{{ formatNumber($total_requested - $total_delivered) }}</b></span>
        </div>
    </div>
@endsection
