@extends('layouts.app')

@section('title', 'تقرير العدادات بالماكينات')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
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
        <h2 class="text-xl font-semibold mb-6 text-gray-700">تقرير العدادات بالماكينات</h2>

        <form action="{{ route('reports.machine_report.result') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">المحطة</label>
                <select name="station_id" id="station_id" class="w-full select2">
                    <option value="">كل المحطات</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">الماكينة</label>
                <select name="machine_id" id="machine_id" class="w-full select2">
                    <option value="">كل الماكينات</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">نوع الوقود</label>
                <select name="fuel_type" class="w-full p-2 border rounded-lg">
                    <option value="">الكل</option>
                    <option value="1">جازولين</option>
                    <option value="2">بنزين</option>
                </select>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#station_id').select2({ placeholder: "كل المحطات", allowClear: true, width: '100%' });
            $('#machine_id').select2({ placeholder: "كل الماكينات", allowClear: true, width: '100%' });

            $('#station_id').on('change', function() {
                let stationId = $(this).val();
                let machineSelect = $('#machine_id');
                machineSelect.empty().append('<option value="">كل الماكينات</option>');
                if (!stationId) return;
                $.get('/api/machines?station_id=' + stationId, function(data) {
                    data.forEach(function(m) {
                        machineSelect.append('<option value="' + m.id + '">' + m.name + '</option>');
                    });
                    machineSelect.trigger('change');
                });
            });
        });
    </script>
@endsection
