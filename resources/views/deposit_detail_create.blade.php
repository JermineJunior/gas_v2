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

            <!-- ملخص مطابقة الوردية (للعرض فقط) -->
            <div class="mb-6 bg-primary-softer border border-primary-soft rounded-xl p-4">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <h3 class="text-base font-bold text-heading">ملخص نهاية الوردية — {{ $station->name }}</h3>
                    <span class="text-xs text-gray-500">للمطابقة فقط — لا يؤثر على التوريد المُدخل</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">إجمالي المبيعات (نهاية الوردية)</p>
                        <p id="shiftSales" class="text-lg font-bold text-heading">—</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">المصروفات</p>
                        <p id="shiftExpenses" class="text-lg font-bold text-accent-strong">—</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">تم توريده</p>
                        <p id="shiftDeposited" class="text-lg font-bold text-heading">—</p>
                    </div>
                    <div class="bg-primary-soft border border-primary rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">الصافي المستحق على الموظف</p>
                        <p id="shiftNet" class="text-2xl font-extrabold text-primary-strong">—</p>
                    </div>
                </div>
            </div>

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
                    <!-- القيم الخام من get_remaining — تُستخدم في إعادة الحساب، المعروض مشتق منها -->
                    <input type="hidden" id="total_old_machine_raw" value="0">
                    <input type="hidden" id="expenses_total_raw" value="0">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد القديم</label>
                        <input id="total_old_machine" type="text" name="total_old_machine" readonly
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                            value="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد الجديد</label>
                        <input type="text" id="total_new_machine" name="total_new_machine" readonly
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
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

            // عداد لطلبات get_remaining: تبديل سريع للموظف يطلق طلبات متداخلة،
            // ونتجاهل أي رد وصل بعد طلب أحدث حتى لا تُكتب أرصدة قديمة
            let remainingRequestId = 0;

            $('#employee_id').on('change', function() {
                let employee_id = $(this).val();
                let station_id = {{ $station->id }};
                const reqId = ++remainingRequestId;

                $.ajax({
                    url: "{{ route('employee.get_remaining') }}",
                    method: "GET",
                    data: {
                        employee_id: employee_id,
                        station_id: station_id
                    },
                    success: function(response) {
                        if (reqId !== remainingRequestId) return; // رد قديم — تجاهله

                        // القيم الخام مخزنة منفصلة عن المعروض؛ الحساب يشتق منها
                        $('#total_old_machine_raw').val(response.total_old_machine);
                        $('#expenses_total_raw').val(response.expenses_total);

                        $('#total_new_machine').val(
                            Number(response.total_new_machine).toLocaleString('en-US', {
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

            // نموذج الرصيد المرحَّل:
            // القديم المعروض = القديم الخام − التوريد المُدخل − المصروفات (قد يكون سالباً بشكل صحيح)
            // الجديد المعروض = القراءات غير المسوّاة كما هي، والمتبقي = القديم المعروض + الجديد
            const NEGATIVE_OLD_CLASSES = 'text-orange-600 dark:text-orange-400 font-semibold';

            function calculateRemaining() {
                let totalOldMachine = parseNumber($('#total_old_machine_raw').val()); // القيمة الخام المرحَّلة
                let totalNewMachine = parseNumber($('#total_new_machine').val());
                let expensesTotal = parseNumber($('#expenses_total_raw').val());

                let totalDeposits = 0;

                // جمع كل التوريدات
                $('.deposit-amount').each(function() {
                    totalDeposits += parseNumber($(this).val());
                });

                let oldDisplayed = totalOldMachine - totalDeposits - expensesTotal;
                let newDisplayed = totalNewMachine;
                let remaining = oldDisplayed + newDisplayed;

                let $oldField = $('#total_old_machine');
                $oldField.val(formatWithCommas(oldDisplayed));
                // سالب القديم حالة صحيحة (التوريد غطى أكثر من الرصيد القديم وحده) — تمييز بلون محايد لا بلون خطأ
                $oldField.toggleClass(NEGATIVE_OLD_CLASSES, oldDisplayed < 0);

                $('#remaining').val(formatWithCommas(remaining));
            }

            // 🔹 ملخص نهاية الوردية — يعيد الحساب عند تغيير التاريخ (عرض فقط)
            const summaryStationId = {{ $station->id }};
            const $shiftDateInput = $('input[name="date"]');

            // عداد لطلبات الملخص: تغيير التاريخ بسرعة يطلق طلبات متداخلة،
            // ونتجاهل أي رد وصل بعد طلب أحدث حتى لا يكتب أرقاماً قديمة
            let summaryRequestId = 0;

            function refreshShiftSummary() {
                const date = $shiftDateInput.val();
                const reqId = ++summaryRequestId;
                if (!date) {
                    $('#shiftSales,#shiftExpenses,#shiftDeposited,#shiftNet').text('—');
                    return;
                }
                $.ajax({
                    url: '{{ route('api.shift-summary') }}',
                    method: 'GET',
                    data: { station_id: summaryStationId, date: date },
                    success: function(res) {
                        if (reqId !== summaryRequestId) return; // رد قديم — تجاهله
                        $('#shiftSales').text(formatWithCommas(res.shift_total));
                        $('#shiftExpenses').text(formatWithCommas(res.expenses_total));
                        $('#shiftDeposited').text(formatWithCommas(res.already_deposited));
                        $('#shiftNet').text(formatWithCommas(res.net_owed));
                    }
                });
            }

            $shiftDateInput.on('change', refreshShiftSummary);
            refreshShiftSummary();
        });
    </script>
@endsection
