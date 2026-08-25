@extends('layouts.app')
@section('title', $deposit->station->name . ' — تعديل التوريدات')
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
    @php
        $allApproved = $deposit->deposit_details->isNotEmpty() && $deposit->deposit_details->every(fn($d) => $d->status == 1);
    @endphp
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        @if ($allApproved)
            <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-lg p-4 text-center font-semibold">
                هذا التوريد معتمد بالكامل ولا يمكن تعديله
            </div>
        @endif

        <form action="{{ route('deposit_detail.update',$deposit->id) }}" method="POST" id="storeForm">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $deposit->station_id }}" name="station_id">

            <!-- ملخص مطابقة الوردية (للعرض فقط) -->
            <div class="mb-6 bg-primary-softer border border-primary-soft rounded-xl p-4">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <h3 class="text-base font-bold text-heading">ملخص نهاية الوردية — {{ $deposit->station->name }}</h3>
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
                    @if (!$allApproved)
                        @can('deposit_details.edit')
                            <button id="addDepositBtn" type="button"
                                class="flex items-center bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">
                                إضافة توريد
                            </button>
                        @endcan
                    @endif
                </div>

                <div id="depositContainer" class="space-y-3">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg relative">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> الموظف</label>
                            <select name="employee_id" id="employee_id" @if($allApproved) disabled @endif>
                                <option value="">قم باختيار الموظف</option>
                                @foreach ($employees as $employee)
                                    <option @selected($employee->id == $deposit->employee_id) value="{{ $employee->id }}">
                                        {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> التاريخ</label>
                            <input type="date" name="date"
                                class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ $deposit->date }}" @if($allApproved) readonly @endif>
                        </div>

                    </div>
                    <!-- الصفوف -->
                    @foreach ($deposit->deposit_details as $deposit_detail)
                        @php $isApproved = $deposit_detail->status == 1; @endphp
                        <div
                            class="deposit-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-primary-soft' }} rounded-lg relative ">
                            <input type="hidden" name="detail_ids[]" value="{{ $deposit_detail->id }}">

                            <!-- مبلغ التوريد -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ التوريد
                                    @if ($isApproved)
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">معتمد</span>
                                    @endif
                                </label>
                                <input type="text" name="deposit_amount[{{ $loop->index }}]" id="deposit_amount_{{ $loop->index }}" placeholder="0.00"
                                    value="{{ number_format($deposit_detail->deposit_amount) }}"
                                    class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                    @if ($isApproved) readonly @endif>
                                @if ($isApproved && $deposit_detail->approver)
                                    <p class="text-xs text-gray-500 mt-1">بواسطة: {{ $deposit_detail->approver->name }} — {{ $deposit_detail->approved_at?->format('Y/m/d') }}</p>
                                @endif
                            </div>

                            <!-- بيان التوريد -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">بيان التوريد</label>
                                <input type="text" name="deposit_desc[{{ $loop->index }}]" id="deposit_desc_{{ $loop->index }}"
                                    placeholder="مثال: توريد يوم الأحد" value="{{ $deposit_detail->deposit_desc }}"
                                    class="deposit-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                    @if ($isApproved) readonly @endif>
                            </div>

                            @if (!$isApproved)
                                @if ($loop->index != 0)
                                    <button type="button"
                                        class="remove-deposit-row text-red-600 font-bold text-lg mt-6">✖</button>
                                @endif
                            @else
                                <span class="text-green-600 font-bold mt-6">✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">التصفية</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                    <!-- القيم الخام المعاد بناؤها من السجل المخزّن — تُستخدم في إعادة الحساب، المعروض مشتق منها -->
                    <input type="hidden" id="total_old_machine_raw" value="{{ $rawOldMachine }}">
                    <input type="hidden" id="expenses_total_raw" value="{{ $formExpenses + $freshExpenses }}">
                    <input type="hidden" id="total_new_machine_fresh" value="{{ $freshSum }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد القديم</label>
                        <input id="total_old_machine" type="text" name="total_old_machine"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly
                            value="{{ number_format($deposit->total_old_machine) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المطلوب من العداد الجديد</label>
                        <input type="text" id="total_new_machine" readonly name="total_new_machine"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                            value="{{ number_format($deposit->total_new_machine) }}">

                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المتبقي</label>
                        <input type="text" id="remaining" readonly name="remaining"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                            value="{{ number_format($deposit->remaining) }}">
                    </div>
                </div>
            </div>

            <div class="flex justify-start space-x-3 space-x-reverse mt-6">
                <!-- زر حفظ -->
                @if (!$allApproved)
                    <button type="submit"
                        class="flex items-center bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                        حفظ
                    </button>
                @endif
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

            // ملاحظة: في صفحة التعديل لا نجلب get_remaining عند تغيير الموظف —
            // مكونات هذه التصفية مجمّدة من السجل المخزّن (قراءاتها سُوّت status=1)،
            // والسيرفر يعيد الاحتساب من قيم مخزّنة موثوقة عند الحفظ.

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
                    <input type="hidden" name="detail_ids[]" value="">
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

            // نموذج الرصيد المرحَّل — نفس صيغة صفحة الإنشاء:
            // القديم المعروض = القديم الخام (معاد بناؤه من السجل) − التوريد − المصروفات، والمتبقي = القديم + الجديد
            const NEGATIVE_OLD_CLASSES = 'text-orange-600 dark:text-orange-400 font-semibold';

            function calculateRemaining() {
                let totalOldMachine = parseNumber($('#total_old_machine_raw').val()); // القيمة الخام المرحَّلة
                let totalNewMachine = parseNumber($('#total_new_machine').val());
                let expensesTotal = parseNumber($('#expenses_total_raw').val());
                let freshNewMachine = parseNumber($('#total_new_machine_fresh').val()); // قراءات ظهرت بعد الإنشاء

                let totalDeposits = 0;

                // جمع كل التوريدات
                $('.deposit-amount').each(function() {
                    totalDeposits += parseNumber($(this).val());
                });

                let oldDisplayed = totalOldMachine - totalDeposits - expensesTotal;
                let newDisplayed = totalNewMachine + freshNewMachine;
                let remaining = oldDisplayed + newDisplayed;

                let $oldField = $('#total_old_machine');
                $oldField.val(formatWithCommas(oldDisplayed));
                // سالب القديم حالة صحيحة (التوريد غطى أكثر من الرصيد القديم وحده) — تمييز بلون محايد لا بلون خطأ
                $oldField.toggleClass(NEGATIVE_OLD_CLASSES, oldDisplayed < 0);

                $('#remaining').val(formatWithCommas(remaining));
            }

            // العرض الابتدائي يعكس الصيغة نفسها فور فتح الصفحة (يشمل القراءات الجديدة إن وُجدت)
            calculateRemaining();

            // 🔹 ملخص نهاية الوردية — يعيد الحساب عند تغيير التاريخ (عرض فقط)
            const summaryStationId = {{ $deposit->station_id }};
            const $shiftDateInput = $('input[name="date"]');

            function refreshShiftSummary() {
                const date = $shiftDateInput.val();
                if (!date) {
                    $('#shiftSales,#shiftExpenses,#shiftDeposited,#shiftNet').text('—');
                    return;
                }
                $.ajax({
                    url: '{{ route('api.shift-summary') }}',
                    method: 'GET',
                    data: { station_id: summaryStationId, date: date },
                    success: function(res) {
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
