@extends('layouts.app')

@section('title', 'نتائج تقرير التناكر')

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
                <h2 class="text-2xl font-bold text-gray-700">نتائج تقرير التناكر</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.tuncker') }}"
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
                        <th class="p-3 text-center">اسم السائق</th>
                        <th class="p-3 text-center">رقم التنكر</th>
                        <th class="p-3 text-center">كمية الوقود</th>
                        <th class="p-3 text-center">نوع الوقود</th>
                        <th class="p-3 text-center"> المورد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $tuncker)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-[#F7ECEE] transition">
                            <td class="p-3 text-center">{{ $loop->iteration }}</td>
                            <td class="p-3 text-center">{{ $tuncker->date->format('Y/m/d') }}</td>

                            @if (!$station)
                                <td class="p-3 text-center">{{ $tuncker->station->name ?? '-' }}</td>
                            @endif
                            <td class="p-3 text-center">{{ $tuncker->driver_name }}</td>
                            <td class="p-3 text-center">{{ $tuncker->tuncker_no }}</td>
                            <td class="p-3 text-center">{{ formatNumber($tuncker->fuel_quantity) }}</td>
                            <td class="p-3 text-center">{{ $tuncker->fuel_type == 1 ? 'جازولين' : 'بنزين'  }}</td>
                            <td class="p-3 text-center">{{ $tuncker->supplier->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">لا توجد بيانات لعرضها</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
