@extends('layouts.app')

@section('title', 'تقرير التوريد')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
    <!-- Select2 CSS -->
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />

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
        <h2 class="text-xl font-semibold mb-6 text-gray-700">تقرير التوريد</h2>

        <form action="{{ route('reports.deposit_detail.result') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf
            <!-- اختيار المحطة -->
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">المحطة</label>
                @cannot('deposit_details.create')
                    <select name="station_id" id="station_id" class="w-full select2" disabled>
                        <option value="">اختر المحطة</option>
                        @foreach ($stations as $station)
                            <option @selected($station->id == auth()->user()->stations[0]->pivot->station_id) value="{{ $station->id }}">{{ $station->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="station_id" value="{{ auth()->user()->stations[0]->pivot->station_id }}">
                @else
                    <select name="station_id" id="station_id" class="w-full select2">
                        <option value="">اختر المحطة</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                        @endforeach
                    </select>
                @endcannot
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

            <!-- فلاتر سريعة -->
            <div class="md:col-span-3 flex flex-wrap justify-center gap-2">
                <button type="button" onclick="setQuickDate('today')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">اليوم</button>
                <button type="button" onclick="setQuickDate('7days')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">آخر 7 أيام</button>
                <button type="button" onclick="setQuickDate('month')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا الشهر</button>
                <button type="button" onclick="setQuickDate('year')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا العام</button>
            </div>

            <!-- زر البحث -->
            <div class="md:col-span-3 flex justify-center mt-4">
                <button type="submit"
                    class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors"
                    @cannot('reports.deposit_detail') disabled @endcannot>
                    بحث
                </button>
            </div>
        </form>
    </div>

@endsection

@section('scripts')
    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script>
        function setQuickDate(preset) {
            var today = new Date();
            var start, end;
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;

            if (preset === 'today') {
                start = end = todayStr;
            } else if (preset === '7days') {
                end = todayStr;
                var d = new Date(today);
                d.setDate(d.getDate() - 6);
                start = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            } else if (preset === 'month') {
                end = todayStr;
                start = yyyy + '-' + mm + '-01';
            } else if (preset === 'year') {
                end = todayStr;
                start = yyyy + '-01-01';
            }
            document.querySelector('input[name="start_date"]').value = start;
            document.querySelector('input[name="end_date"]').value = end;
        }
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
