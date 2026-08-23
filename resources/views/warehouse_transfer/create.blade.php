@extends('layouts.app')

@section('title', 'اضافة تحويل مستودع')

@section('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 5px 10px; font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">اضافة تحويل مستودع</h2>
            <a href="{{ route('warehouse_transfers.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">رجوع</a>
        </div>

        <form id="transferForm" action="{{ route('warehouse_transfers.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">من مستودع</label>
                    <select name="from_warehouse_id" id="from_warehouse_id" class="w-full select2" required>
                        <option value="">اختر المستودع المصدر</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">الى مستودع</label>
                    <select name="to_warehouse_id" id="to_warehouse_id" class="w-full select2" required>
                        <option value="">اختر المستودع الوجهة</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">نوع الوقود</label>
                    <select name="fuel_type" id="fuel_type" class="w-full p-2 border rounded-lg" required>
                        <option value="1" {{ old('fuel_type') == 1 ? 'selected' : '' }}>جازولين</option>
                        <option value="2" {{ old('fuel_type') == 2 ? 'selected' : '' }}>بنزين</option>
                    </select>
                    <div id="stockLabel" class="mt-1 text-sm font-semibold text-primary-strong hidden">
                        رصيد المصدر: <span id="stockValue">0</span>
                    </div>
                    @error('fuel_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">الكمية</label>
                    <input type="text" name="quantity" value="{{ old('quantity') }}" class="w-full p-2 border rounded-lg" required>
                    @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">التاريخ</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full p-2 border rounded-lg" required>
                    @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">ملاحظة</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="w-full p-2 border rounded-lg">
                    @error('note') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-primary-strong text-white px-8 py-3 rounded-lg shadow hover:bg-primary-strong transition font-semibold">حفظ التحويل</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            attachLiveFormatter(document.querySelector('input[name="quantity"]'));

            var currentStock = 0;

            function syncWarehouses() {
                var fromId = $('#from_warehouse_id').val();
                var $to = $('#to_warehouse_id');
                var toVal = $to.val();

                $to.find('option[value]').each(function() {
                    var $opt = $(this);
                    if ($opt.val() === '' || $opt.val() === undefined) return;
                    if ($opt.val() === fromId) {
                        $opt.prop('disabled', true);
                    } else {
                        $opt.prop('disabled', false);
                    }
                });

                if (toVal === fromId) {
                    $to.val('').trigger('change');
                }

                $to.trigger('change.select2');
            }

            function fetchStock() {
                var warehouseId = $('#from_warehouse_id').val();
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

            $('#from_warehouse_id').on('change', function() {
                syncWarehouses();
                fetchStock();
            });
            $('#fuel_type').on('change', fetchStock);
            syncWarehouses();
            fetchStock();

            $('#transferForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var quantity = parseNumber($('input[name="quantity"]').val());
                var fromText = $('#from_warehouse_id option:selected').text();
                var toText = $('#to_warehouse_id option:selected').text();
                var fuelTypeText = $('#fuel_type option:selected').text();

                if (!$('#from_warehouse_id').val()) {
                    Swal.fire({ icon: 'warning', title: 'خطأ', text: 'يرجى اختيار المستودع المصدر', confirmButtonText: 'حسناً' });
                    return;
                }

                if (!$('#to_warehouse_id').val()) {
                    Swal.fire({ icon: 'warning', title: 'خطأ', text: 'يرجى اختيار المستودع الوجهة', confirmButtonText: 'حسناً' });
                    return;
                }

                if ($('#from_warehouse_id').val() === $('#to_warehouse_id').val()) {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'لا يمكن التحويل من مستودع الى نفسه', confirmButtonText: 'حسناً' });
                    return;
                }

                if (quantity <= 0) {
                    Swal.fire({ icon: 'warning', title: 'خطأ', text: 'يرجى ادخال كمية تحويل صحيحة', confirmButtonText: 'حسناً' });
                    return;
                }

                if (currentStock <= 0) {
                    Swal.fire({ icon: 'error', title: 'المستودع فارغ', text: 'لا يوجد رصيد متوفر من نوع ' + fuelTypeText + ' في ' + fromText, confirmButtonText: 'حسناً' });
                    return;
                }

                if (quantity > currentStock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'الكمية تتجاوز الرصيد',
                        html: 'الكمية المطلوبة: <b>' + fmt(quantity) + '</b><br>الرصيد المتوفر: <b>' + fmt(currentStock) + '</b>',
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'تأكيد التحويل',
                    html: 'تحويل <b>' + fmt(quantity) + '</b> من ' + fuelTypeText + '<br>من: <b>' + fromText + '</b><br>الى: <b>' + toText + '</b>',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، حوّل',
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
