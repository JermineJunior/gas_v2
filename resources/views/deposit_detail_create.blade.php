@extends('layouts.app')

@section('title', $station->name . ' — تسجيل التوريدات')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .text-danger {
            color: red !important;
            font-size: 0.875rem;
            margin-top: 4px;
            display: block;
        }

        .select2-container .select2-selection--single {
            height: 42px !important;
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding-left: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 8px;
        }
    </style>
@endsection

@section('content')

    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        <form action="{{ route('deposit_detail.store') }}" method="POST" id="storeForm">
            @csrf
            <input type="hidden" value="{{ $station->id }}" name="station_id">

            <div class="mb-6">
                <div class="flex justify-end items-center mb-4">
                    {{-- <h2 class="text-xl font-semibold text-gray-800">بنود التوريد</h2> --}}
                    <button id="addDepositBtn" type="button"
                        class="flex items-center bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">
                        إضافة توريد
                    </button>
                </div>

                <div id="depositContainer" class="space-y-3">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg relative">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> الموظف</label>
                            <select name="employee_id" id="employee_id">
                                <option value="">قم باختيار الموظف</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> التاريخ</label>
                            <input type="date" name="date"
                                class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                    </div>
                    <!-- الصف الأساسي -->
                    <div
                        class="deposit-item grid grid-cols-[1fr,1.5fr] gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                        <!-- مبلغ التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ التوريد</label>
                            <input type="text" name="deposit_amount[0]" id="deposit_amount_0" placeholder="0.00"
                                class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- بيان التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">بيان التوريد</label>
                            <input type="text" name="deposit_desc[0]" id="deposit_desc_0"
                                placeholder="مثال: توريد يوم الأحد"
                                class="deposit-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">التصفية</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد القديم</label>
                        <input id="total_old_machine" type="text" name="total_old_machine"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد الجديد</label>
                        <input type="text" id="total_new_machine" name="total_new_machine"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                            value="0">

                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المتبقي</label>
                        <input type="text" id="remaining" readonly name="remaining"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-start space-x-3 space-x-reverse mt-6">
                <!-- زر حفظ -->
                <button type="submit"
                    class="flex items-center bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                    حفظ
                </button>
            </div>

        </form>
    </div>

@endsection

@section('scripts')
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    @include('messages')


    <script>
        $(document).ready(function() {

            $('#employee_id').select2({
                width: '100%',
                placeholder: 'اختر اسم الموظف'
            });

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

            const depositContainer = document.getElementById('depositContainer');
            const addDepositBtn = document.getElementById('addDepositBtn');
            const storeForm = document.getElementById('storeForm');
            const submitBtn = storeForm.querySelector('button[type="submit"]');

            $('#storeForm').validate({
                rules: {
                    employee_id: {
                        required: true
                    },
                    date: {
                        required: true
                    },
                    'deposit_amount[0]': {
                        required: true
                    }
                },
                messages: {
                    employee_id: {
                        required: "يجب ادخال اسم الموظف"
                    },
                    date: {
                        required: "يجب ادخال التاريخ"
                    },
                    'deposit_amount[0]': {
                        required: "يجب ادخال قيمة مبلغ التوريد"
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
                errorPlacement: function(error, element) {
                    if (element.hasClass("select2-hidden-accessible")) {
                        error.insertAfter(element.next('.select2'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {
                    const selectors = [
                        'input.deposit-amount',
                        'input#total_old_machine',
                        'input#total_new_machine',
                        'input#remaining',
                    ].join(',');

                    form.querySelectorAll(selectors).forEach(el => {
                        if (el && el.value) {
                            el.value = String(el.value).replace(/,/g, '');
                        }
                    });

                    const submitBtn = form.querySelector('button[type="submit"]');

                    submitBtn.disabled = true;

                    submitBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="w-5 h-5 mr-2 animate-spin" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor">
                            <circle class="opacity-25" 
                                    cx="12" cy="12" r="10" 
                                    stroke-width="4"></circle>
                            <path class="opacity-75" 
                                fill="currentColor" 
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        جاري حفظ البيانات...
                    `;

                    form.submit();

                }
            });

            $('#employee_id').on('change', function() {
                let employee_id = $(this).val();
                let station_id = {{ $station->id }};

                $.ajax({
                    url: "{{ route('employee.get_remaining') }}",
                    method: "GET",
                    data: {
                        employee_id: employee_id,
                        station_id: station_id
                    },
                    success: function(response) {
                        $('#total_new_machine').val(
                            Number(response.total_new_machine).toLocaleString('en-US', {
                                maximumFractionDigits: 2
                            })
                        );

                        $('#total_old_machine').val(
                            Number(response.total_old_machine).toLocaleString('en-US', {
                                maximumFractionDigits: 2
                            })
                        );
                        calculateRemaining();
                    }
                });
            });

            $(document).on('input', '.deposit-amount', function() {
                let value = parseNumber($(this).val());
                $(this).val(formatWithCommas(value));
                calculateRemaining();
            });

            function attachDepositRow(row, idx) {
                const amountInput = row.querySelector('.deposit-amount');
                const descInput = row.querySelector('.deposit-desc');
                const removeBtn = row.querySelector('.remove-deposit-row');

                // تنسيق الأرقام أثناء الكتابة (لو عندك attachLiveFormatter)
                if (amountInput && typeof attachLiveFormatter === 'function') {
                    attachLiveFormatter(amountInput);
                }

                // زر الحذف
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        row.remove();
                        calculateRemaining();
                    });
                }
            }

            // 🔹 إنشاء صف توريد جديد
            function addDepositRow() {
                let index = depositContainer.querySelectorAll('.deposit-item').length;
                const row = document.createElement('div');
                row.className =
                    "deposit-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 bg-primary-soft rounded-lg relative";
                row.innerHTML = `
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ التوريد</label>
                        <input type="text" name="deposit_amount[${index}]" id="deposit_amount_${index}" placeholder="0.00"
                            class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">بيان التوريد</label>
                        <input type="text" name="deposit_desc[${index}]" id="deposit_desc_${index}" placeholder="مثال: توريد إضافي"
                            class="deposit-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <button type="button" class="remove-deposit-row text-red-600 font-bold text-lg mt-6">✖</button>
                `;
                depositContainer.appendChild(row);
                attachDepositRow(row, index);
                $('.check_id').select2({
                    width: '100%',
                    placeholder: 'اختر الشيك'
                });
            }

            // 🔹 عند الضغط على زر الإضافة
            if (addDepositBtn) addDepositBtn.addEventListener('click', addDepositRow);

            // 🔹 تهيئة الصف الأساسي الموجود
            depositContainer.querySelectorAll(':scope > .deposit-item').forEach((row, idx) => {
                attachDepositRow(row, idx);
            });

            function calculateRemaining() {

                let oldMachine = parseNumber($('#total_old_machine').val()); // العداد القديم
                let newMachine = parseNumber($('#total_new_machine').val()); // العداد الجديد

                let totalDeposits = 0;

                // جمع كل التوريدات
                $('.deposit-amount').each(function() {
                    totalDeposits += parseNumber($(this).val());
                });

                let remaining = (oldMachine + newMachine) - totalDeposits;

                // عرض النتيجة مع تنسيق
                $('#remaining').val(formatWithCommas(remaining));
            }

            $(document).on('input', '#total_old_machine', function() {
                let value = $(this).val().replace(/,/g, ''); // نحذف الفواصل
                let formatted = formatWithCommas(value);
                $(this).val(formatted);
                calculateRemaining();
            });

            $(document).on('input', '#total_new_machine', function() {
                let value = $(this).val().replace(/,/g, ''); // نحذف الفواصل
                let formatted = formatWithCommas(value);
                $(this).val(formatted);
                calculateRemaining();
            });
        });
    </script>
@endsection
