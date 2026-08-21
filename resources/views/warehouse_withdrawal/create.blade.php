@extends('layouts.app')

@section('title', 'اضافة سحب من المستودع')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 5px 10px; font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">اضافة سحب من المستودع</h2>
            <a href="{{ route('warehouse_withdrawals.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">رجوع</a>
        </div>

        <form id="withdrawalForm" action="{{ route('warehouse_withdrawals.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المستودع</label>
                    <select name="warehouse_id" id="warehouse_id" class="w-full select2" required>
                        <option value="">اختر المستودع</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع الوقود</label>
                    <select name="fuel_type" id="fuel_type" class="w-full p-2 border rounded-lg" required>
                        <option value="">اختر نوع الوقود</option>
                        <option value="1" {{ old('fuel_type') == 1 ? 'selected' : '' }}>جازولين</option>
                        <option value="2" {{ old('fuel_type') == 2 ? 'selected' : '' }}>بنزين</option>
                    </select>
                    <div id="stockLabel" class="mt-1 text-sm font-semibold text-primary-strong hidden">
                        الرصيد المتوفر: <span id="stockValue">0</span>
                    </div>
                    @error('fuel_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم السائق</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name') }}" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('driver_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم العربية</label>
                    <input type="text" name="car_number" value="{{ old('car_number') }}" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('car_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الكمية</label>
                    <input type="text" name="amount" value="{{ old('amount') }}" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('amount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ</label>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="note" rows="2" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('note') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition">
                    حفظ
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        function fmt(n) { return Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 }); }

        function cleanNumber(str) {
            return String(str || '').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        }
        function formatWithCommas(numStr) {
            if (!numStr) return '';
            let s = String(numStr).replace(/,/g, '');
            if (s === '') return '';
            let parts = s.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
        }
        function parseNumber(str) {
            let cleaned = String(str || '').replace(/,/g, '');
            let n = parseFloat(cleaned);
            return isNaN(n) ? 0 : n;
        }
        function attachLiveFormatter(input) {
            if (!input) return;
            input.addEventListener('input', function() {
                let raw = cleanNumber(input.value);
                input.value = formatWithCommas(raw);
            });
            input.value = formatWithCommas(cleanNumber(input.value));
        }

        $(document).ready(function() {
            $('.select2').select2({ placeholder: "اختر المستودع", allowClear: true, width: '100%' });
            attachLiveFormatter(document.querySelector('input[name="amount"]'));

            var currentStock = 0;

            function fetchStock() {
                var warehouseId = $('#warehouse_id').val();
                var fuelType = $('#fuel_type').val();
                if (warehouseId && fuelType) {
                    $.get('{{ route("api.warehouse-stock") }}', { warehouse_id: warehouseId, fuel_type: fuelType }, function(data) {
                        currentStock = parseFloat(data.stock) || 0;
                        $('#stockValue').text(fmt(currentStock));
                        $('#stockLabel').removeClass('hidden');
                    });
                } else {
                    currentStock = 0;
                    $('#stockLabel').addClass('hidden');
                }
            }

            $('#warehouse_id').on('change', fetchStock);
            $('#fuel_type').on('change', fetchStock);
            fetchStock();

            $('#withdrawalForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var amount = parseNumber($('input[name="amount"]').val());
                var fuelTypeText = $('#fuel_type option:selected').text();
                var warehouseText = $('#warehouse_id option:selected').text();

                if (currentStock <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'المستودع فارغ',
                        text: 'لا يوجد رصيد متوفر من نوع ' + fuelTypeText + ' في ' + warehouseText,
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                if (amount <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'خطأ',
                        text: 'يرجى ادخال كمية سحب صحيحة',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                if (amount > currentStock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'الكمية تتجاوز الرصيد',
                        html: 'الكمية المطلوبة: <b>' + fmt(amount) + '</b><br>الرصيد المتوفر: <b>' + fmt(currentStock) + '</b>',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'تأكيد السحب',
                    html: 'هل تريد سحب <b>' + fmt(amount) + '</b> من ' + fuelTypeText + '<br>الرصيد المتوفر: <b>' + fmt(currentStock) + '</b><br>الرصيد بعد السحب: <b>' + fmt(currentStock - amount) + '</b>',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، اسحب',
                    cancelButtonText: 'إلغاء'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.off('submit');
                        form[0].submit();
                    }
                });
            });
        });
    </script>
@endsection
