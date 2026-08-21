@extends('layouts.app')

@section('title', 'تقرير المستودعات')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 5px 10px; font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">تقرير المستودعات</h2>

        <form action="{{ route('reports.warehouse.result') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">المستودع</label>
                    <select name="warehouse_id" class="w-full select2" required>
                        <option value="">اختر المستودع</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">نوع الوقود</label>
                    <select name="fuel_type" class="w-full p-2 border rounded-lg" required>
                        <option value="">اختر نوع الوقود</option>
                        <option value="1">جازولين</option>
                        <option value="2">بنزين</option>
                    </select>
                    @error('fuel_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">من تاريخ</label>
                    <input type="date" name="start_date" class="w-full p-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">الى تاريخ</label>
                    <input type="date" name="end_date" class="w-full p-2 border rounded-lg">
                </div>
            </div>

            <!-- فلاتر سريعة -->
            <div class="flex flex-wrap justify-center gap-2">
                <button type="button" onclick="setQuickDate('today')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">اليوم</button>
                <button type="button" onclick="setQuickDate('7days')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">آخر 7 أيام</button>
                <button type="button" onclick="setQuickDate('month')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا الشهر</button>
                <button type="button" onclick="setQuickDate('year')" class="quick-date-btn bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">هذا العام</button>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-primary-strong text-white px-8 py-3 rounded-lg shadow hover:bg-primary-strong transition font-semibold">عرض التقرير</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
            $('.select2').select2({ placeholder: "اختر المستودع", allowClear: true, width: '100%' });
        });
    </script>
@endsection
