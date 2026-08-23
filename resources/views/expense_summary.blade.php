@extends('layouts.app')

@section('title', 'ملخص المصروفات')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 mt-10 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-6 text-gray-700">ملخص المصروفات</h2>

        <form action="{{ route('reports.expense_summary.result') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">المحطة</label>
                <select name="station_id" class="w-full p-2 border rounded-lg" required>
                    <option value="">اختر المحطة</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">تاريخ البداية</label>
                <input type="date" name="start_date" class="w-full p-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">تاريخ النهاية</label>
                <input type="date" name="end_date" class="w-full p-2 border rounded-lg">
            </div>

            <div class="md:col-span-3 flex flex-wrap justify-center gap-2">
                <button type="button" onclick="setQuickDate('today')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">اليوم</button>
                <button type="button" onclick="setQuickDate('7days')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">آخر 7 أيام</button>
                <button type="button" onclick="setQuickDate('month')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا الشهر</button>
                <button type="button" onclick="setQuickDate('year')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا العام</button>
            </div>

            <div class="md:col-span-3 flex justify-center mt-4">
                <button type="submit" class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                    بحث
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function setQuickDate(preset) {
            var today = new Date();
            var start, end;
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;
            if (preset === 'today') { start = end = todayStr; }
            else if (preset === '7days') { end = todayStr; var d = new Date(today); d.setDate(d.getDate() - 6); start = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); }
            else if (preset === 'month') { end = todayStr; start = yyyy + '-' + mm + '-01'; }
            else if (preset === 'year') { end = todayStr; start = yyyy + '-01-01'; }
            document.querySelector('input[name="start_date"]').value = start;
            document.querySelector('input[name="end_date"]').value = end;
        }
    </script>
@endsection
