@extends('layouts.app')

@section('title', $tuncker->station->name . ' — تفاصيل التنكر')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('content')
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        <div class="flex justify-between items-start mb-6">
            <div class="flex items-center">
                <div class="flex items-center gap-3 mb-6">
                    <a href="{{ route('tuncker.index', $tuncker->station_id) }}"
                        class="text-gray-400 hover:text-gray-600 text-sm">← قائمة التناكر</a>
                </div>
                <div
                    class="w-12 h-12 bg-gradient-to-r bg-primary-strong rounded-xl flex items-center justify-center mr-3 ml-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                        <path d="M15 18H9" />
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                        <circle cx="17" cy="18" r="2" />
                        <circle cx="7" cy="18" r="2" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $tuncker->station->name }}</h1>
                    <p class="text-gray-600">تفاصيل التنكر رقم {{ $tuncker->tuncker_no }}</p>
                </div>
            </div>
        </div>

        <!-- معلومات عامة -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-primary-soft rounded-lg mb-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">التاريخ</p>
                <p class="font-bold text-gray-800">{{ $tuncker->date->format('Y/m/d') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">رقم التنكر</p>
                <p class="font-bold text-gray-800">{{ $tuncker->tuncker_no }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">اسم السائق</p>
                <p class="font-bold text-gray-800">{{ $tuncker->driver_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">نوع الوقود</p>
                <p class="font-bold text-gray-800">{{ $tuncker->fuel_type == 1 ? 'جازولين' : 'بنزين' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">كمية الوقود (لتر)</p>
                <p class="font-bold text-gray-800">{{ number_format($tuncker->fuel_quantity) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">المورد</p>
                <p class="font-bold text-gray-800">{{ $tuncker->supplier?->name ?? '—' }}</p>
            </div>
        </div>

        <!-- الآبار والصور -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">الآبار والصور</h2>

            @forelse ($tuncker->stockDetail as $stockDetail)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start p-4 bg-gray-50 border border-gray-200 rounded-lg mb-3">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">البير</p>
                        <p class="font-bold text-gray-800">{{ $stockDetail->stock?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">الكمية المفرغة (لتر)</p>
                        <p class="font-bold text-gray-800">{{ number_format($stockDetail->qty) }}</p>
                    </div>
                </div>

                @if ($stockDetail->photos->count())
                    <div class="flex flex-wrap gap-3 mb-4">
                        @foreach ($stockDetail->photos as $photo)
                            <a href="{{ asset('storage/' . $photo->path) }}" target="_blank"
                                title="{{ basename($photo->path) }}">
                                <img src="{{ asset('storage/' . $photo->path) }}"
                                    class="w-40 h-40 object-cover rounded-lg border hover:opacity-80 transition">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 mb-4">لا توجد صور لهذا البير</p>
                @endif
            @empty
                <div class="text-center bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-4">
                    لا توجد آبار مرتبطة بهذا التنكر.
                </div>
            @endforelse
        </div>
    </div>
@endsection