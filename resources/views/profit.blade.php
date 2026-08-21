@extends('layouts.app')

@section('title', 'تقرير الارباح')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 mt-10 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-6 text-gray-700">تقرير الارباح</h2>

        <form action="{{ route('reports.profit.result') }}" method="POST"
            class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf
            <!-- اختيار المحطة -->
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">المحطة</label>
                <select name="station_id" id="station_id" class="w-full select2">
                    <option value="">اختر المحطة</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- تاريخ البداية -->
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">تاريخ البداية</label>
                <input type="date" name="start_date" class="w-full p-2 border rounded-lg">
            </div>

            <!-- تاريخ النهاية -->
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">تاريخ النهاية</label>
                <input type="date" name="end_date" class="w-full p-2 border rounded-lg">
            </div>

            <!-- زر البحث -->
            <div class="md:col-span-3 flex justify-center mt-4">
                <button type="submit"
                    class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                    بحث
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "اختر المحطة",
                allowClear: true,
                width: '100%'
            });

            // قائمة الموبايل (الهامبرجر)
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });

                // إغلاق القائمة لو ضغطت خارجها
                window.addEventListener('click', function(e) {
                    if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endsection
