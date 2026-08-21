@extends('layouts.app')

@section('title', 'نتائج تقرير التكلفة')

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
                <h2 class="text-2xl font-bold text-gray-700">نتائج تقرير التكلفة</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.cost') }}"
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
                <p class="font-semibold">{{ $station->name ?? 'كل المحطات' }}</p>
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

        <!-- جدول النتائج -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead>
                    <tr class="bg-primary-strong text-white text-sm">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        @if (!$station)
                            <th class="p-3 text-center">المحطة</th>
                        @endif
                        <th class="p-3 text-center">شراء الجازولين</th>
                        <th class="p-3 text-center">الترحيل</th>
                        <th class="p-3 text-center">الاجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $cost)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $loop->iteration }}</td>
                            <td class="p-3 text-center">{{ $cost->date->format('Y/m/d') }}</td>
                            @if (!$station)
                                <td class="p-3 text-center">{{ $cost->station->name ?? '-' }}</td>
                            @endif
                            <td class="p-3 text-center">{{ formatNumber($cost->buy_diesel) }} جنيه</td>
                            <td class="p-3 text-center">{{ formatNumber($cost->transfer_diesel) }} جنيه</td>
                            <td class="p-3 text-center font-semibold text-green-600">
                                {{ formatNumber($cost->cost_total) }} جنيه
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

        <!-- ملخص -->
        @if ($operations->count() > 0)
            <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">إجمالي التكلفة:</span>
                <span class="text-xl font-bold text-primary-strong">
                    {{ formatNumber($operations->sum('cost_total')) }} جنيه
                </span>
            </div>
        @endif
    </div>
@endsection
