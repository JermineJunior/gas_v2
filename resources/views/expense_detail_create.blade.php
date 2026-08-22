@extends('layouts.app')

@section('title', $station->name . ' — تسجيل المصروفات')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
    <style>
        .text-danger {
            color: red !important;
            font-size: 0.875rem;
            margin-top: 4px;
            display: block;
        }
    </style>
@endsection

@section('content')

    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        <form action="{{ route('expense.store') }}" method="POST" id="storeForm">
            @csrf
            <input type="hidden" value="{{ $station->id }}" name="station_id">

            <div class="mb-6">
                <div class="flex justify-end items-center mb-4">
                    <button id="addExpenseBtn" type="button"
                        class="flex items-center bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">
                        إضافة مصروف
                    </button>
                </div>

                <div id="expenseContainer" class="space-y-3">
                    <!-- التاريخ -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg relative">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">التاريخ</label>
                            <input type="date" name="date" required
                                class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ now()->toDateString() }}">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الإجمالي</label>
                            <input type="text" id="grandTotal" readonly
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 font-bold">
                        </div>
                    </div>

                    <!-- الصف الأساسي -->
                    <div
                        class="expense-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ المصروف</label>
                            <input type="text" name="expense_amount[0]" id="expense_amount_0" placeholder="0.00"
                                class="expense-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">بيان المصروف</label>
                            <input type="text" name="expense_desc[0]" id="expense_desc_0"
                                placeholder="مثال: صيانة مضخة"
                                class="expense-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <span></span>
                    </div>
                </div>
            </div>

            <div class="flex justify-start space-x-3 space-x-reverse mt-6">
                <button type="submit"
                    class="flex items-center bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                    حفظ
                </button>
            </div>

        </form>
    </div>

@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    @include('messages')

    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>

    <script>
        $(document).ready(function() {

            function formatWithCommas(num) {
                if (num === null || num === '' || isNaN(Number(num))) return '';
                return Number(num).toLocaleString('en-US', {
                    maximumFractionDigits: 2
                });
            }

            function parseNumber(str) {
                let cleaned = String(str || '').replace(/,/g, '');
                let n = parseFloat(cleaned);
                return isNaN(n) ? 0 : n;
            }

            const expenseContainer = document.getElementById('expenseContainer');
            const addExpenseBtn = document.getElementById('addExpenseBtn');
            const storeForm = document.getElementById('storeForm');

            $('#storeForm').validate({
                rules: {
                    date: {
                        required: true
                    },
                    'expense_amount[0]': {
                        required: true
                    }
                },
                messages: {
                    date: {
                        required: "يجب ادخال التاريخ"
                    },
                    'expense_amount[0]': {
                        required: "يجب ادخال قيمة مبلغ المصروف"
                    }
                },
                errorElement: "span",
                errorClass: "text-red-600 text-sm",
                highlight: function(element) {
                    $(element).addClass("border-red-500");
                },
                unhighlight: function(element) {
                    $(element).removeClass("border-red-500");
                },
                submitHandler: function(form) {
                    form.querySelectorAll('input.expense-amount').forEach(el => {
                        if (el && el.value) el.value = String(el.value).replace(/,/g, '');
                    });

                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `جاري حفظ البيانات...`;
                    form.submit();
                }
            });

            function calculateTotal() {
                let total = 0;
                $('.expense-amount').each(function() {
                    total += parseNumber($(this).val());
                });
                $('#grandTotal').val(formatWithCommas(total));
            }

            $(document).on('input', '.expense-amount', function() {
                let value = parseNumber($(this).val());
                $(this).val(formatWithCommas(value));
                calculateTotal();
            });

            function attachExpenseRow(row) {
                const amountInput = row.querySelector('.expense-amount');
                const removeBtn = row.querySelector('.remove-expense-row');

                if (amountInput && typeof attachLiveFormatter === 'function') {
                    attachLiveFormatter(amountInput);
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        row.remove();
                        calculateTotal();
                    });
                }
            }

            // 🔹 إنشاء صف مصروف جديد
            function addExpenseRow() {
                let index = expenseContainer.querySelectorAll('.expense-item').length;
                const row = document.createElement('div');
                row.className =
                    "expense-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 bg-primary-soft rounded-lg relative";
                row.innerHTML = `
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ المصروف</label>
                        <input type="text" name="expense_amount[\${index}]" id="expense_amount_\${index}" placeholder="0.00"
                            class="expense-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">بيان المصروف</label>
                        <input type="text" name="expense_desc[\${index}]" id="expense_desc_\${index}" placeholder="مثال: صيانة مضخة"
                            class="expense-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <button type="button" class="remove-expense-row text-red-600 font-bold text-lg mt-6">✖</button>
                `;
                expenseContainer.appendChild(row);
                attachExpenseRow(row);
            }

            if (addExpenseBtn) addExpenseBtn.addEventListener('click', addExpenseRow);

            depositContainerInit();

            function depositContainerInit() {
                expenseContainer.querySelectorAll(':scope > .expense-item').forEach(row => {
                    attachExpenseRow(row);
                });
            }

            calculateTotal();
        });
    </script>
@endsection
