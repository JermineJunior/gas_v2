@extends('layouts.app')

@section('title', $machine_detail->station->name . ' — تعديل عدادات')

@section('body-class', 'bg-gray-100 p-6')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        <form action="{{ route('machine_detail.update', $machine_detail->id) }}" method="POST" id="storeForm">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $machine_detail->station_id }}" name="station_id">

            <div class="mb-6">
                <div class="flex justify-end items-center mb-4"> {{-- <h2 class="text-xl font-semibold text-gray-800">تفاصيل البيع </h2> --}}
                    <button type="button" id="addFuelInvoiceBtn"
                        class="flex items-center bg-primary-strong text-white px-3 py-2 rounded-lg hover:bg-primary-strong">
                        إضافة عداد جديد
                    </button>
                </div>

                <div id="stocksInfoContainer" class="mb-4 space-y-2"></div>

                <div id="fuelInvoicesContainer" class="space-y-4">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg relative">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> الموظف</label>
                            <select name="employee_id" id="employee_id">
                                <option value="">قم باختيار الموظف</option>
                                @foreach ($employees as $employee)
                                    <option @selected($employee->id == $machine_detail->station_id) value="{{ $employee->id }}">
                                        {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700"> التاريخ</label>
                            <input type="date" name="date"
                                class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ $machine_detail->date->format('Y-m-d') }}">
                        </div>

                    </div>
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-7 gap-4 bg-primary-soft rounded-lg relative">

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينة</label>
                            <div class="flex items-center gap-2">
                                {{-- زر الإضافة (تم تعطيله - الإضافة الآن من صفحة تهيئة المحطة)
                                <button type="button"
                                    class="add-machine-btn flex items-center justify-center bg-primary-strong text-white rounded-lg p-2 hover:bg-primary-strong transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                                --}}

                                <select name="machine_id[0]"
                                    class="machine w-full p-2 border border-gray-300 rounded-lg select2" data-prev-machine="{{ $machine_detail->machine_id }}" required>
                                    <option value="">اختر العداد</option>
                                    @foreach ($machines as $machine)
                                        <option @selected($machine_detail->machine_id == $machine->id) value="{{ $machine->id }}">
                                            {{ $machine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="stock-label block text-xs text-green-700 mt-1 font-semibold"></span>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">المسدسات</label>
                            <div class="flex items-center">
                                {{-- زر الإضافة (تم تعطيله - الإضافة الآن من صفحة تهيئة المحطة)
                                <button type="button"
                                    class="add-gun-btn flex items-center justify-center bg-primary-strong text-white rounded-lg p-2 hover:bg-primary-strong transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                                --}}

                                <select name="gun_id[0]"
                                    class="gun w-full p-2 border border-gray-300 rounded-lg select2" required>
                                    @foreach ($guns as $gun)
                                        <option @selected($gun->id == $machine_detail->gun_id) value="{{ $gun->id }}">
                                            {{ $gun->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mr-5">
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد البداية</label>
                            <input type="text" name="start_counter[0]" placeholder="0"
                                value="{{ number_format($machine_detail->start_counter) }}"
                                class="start-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد النهاية</label>
                            <input type="text" name="end_counter[0]" placeholder="0"
                                value="{{ number_format($machine_detail->end_counter) }}"
                                class="end-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- الصافي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">صافي اللتر</label>
                            <input type="hidden" name="is_rollover[0]" value="{{ $machine_detail->is_rollover ? '1' : '0' }}" class="is-rollover">
                            <input type="text" name="net[0]" readonly
                                value="{{ number_format($machine_detail->net) }}"
                                class="net w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        <!-- السعر -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">سعر اللتر</label>
                            <input type="text" name="price[0]" placeholder="0.00"
                                value="{{ number_format($machine_detail->price) }}"
                                class="price w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- الإجمالي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الإجمالي</label>
                            <input type="text" name="total[0]" readonly
                                value="{{ number_format($machine_detail->total) }}"
                                class="total w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>
                    </div>

                </div>


                <!-- الإجماليات -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">إجمالي اللترات</label>
                        <input type="text" id="grandLiters" readonly
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">إجمالي المبلغ</label>
                        <input type="text" id="grandTotal" readonly
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

    <!-- المودال -->
    <div id="addMachineModal" class="fixed inset-0 bg-black/50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-80 relative">
            <button id="closeModalBtn" class="absolute top-2 left-2 text-gray-500 hover:text-gray-700">✖</button>

            <h2 class="text-lg font-semibold text-gray-800 mb-4">إضافة ماكينة جديدة</h2>

            <form id="addMachineForm">
                <!-- اسم الماكينة -->
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">اسم الماكينة</label>
                    <input type="text" name="name" id="machineName" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <!-- العملية (مخفي) -->
                <input type="hidden" name="station_id" id="stationId"
                    value="{{ $machine_detail->station_id ?? '' }}">

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="bg-primary-strong text-white px-4 py-2 rounded-lg hover:bg-primary-strong transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="addGunModal" class="fixed inset-0 bg-black/50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-80 relative">
            <button id="closeModalBtn" class="absolute top-2 left-2 text-gray-500 hover:text-gray-700">✖</button>

            <h2 class="text-lg font-semibold text-gray-800 mb-4">إضافة مسدس جديد</h2>

            <form id="addGunForm">
                <!-- اسم الماكينة -->
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">اسم المسدس</label>
                    <input type="text" name="name" id="gunName" required
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <!-- العملية (مخفي) -->
                <input type="hidden" name="station_id" id="stationId"
                    value="{{ $machine_details->station_id ?? '' }}">
                <input type="hidden" name="machine_id" id="machineId"
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="bg-primary-strong text-white px-4 py-2 rounded-lg hover:bg-primary-strong transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    <script>
        $(document).ready(function() {
            let currentSelectMachine = null;
            let currentSelectGun = null;

            if ($.fn.select2) {
                $('.machine').select2({
                    width: '100%',
                    placeholder: 'اختر الماكينة'
                });

                $('.gun').select2({
                    width: '100%',
                    placeholder: 'اختر المسدس'
                });

                $('#employee_id').select2({
                    width: '100%',
                    placeholder: 'اختر الموظف'
                });
            }

            function cleanNumberRaw(str) {
                return String(str || '').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
            }

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

            function attachLiveFormatter(input, onChange) {
                if (!input) return;

                function update() {
                    // نحافظ على الأرقام فقط ثم نعرضها مُنسقة
                    let raw = cleanNumberRaw(input.value);
                    input.value = formatWithCommas(raw);
                    if (typeof onChange === 'function') onChange();
                }
                // الحدث input ليعمل أثناء الكتابة
                input.addEventListener('input', update);
                // تطبيق فورمات ابتدائي لو فيه قيمة عند التحميل
                update();
            }

            const container = document.getElementById('fuelInvoicesContainer');
            const addInvoiceBtn = document.getElementById('addFuelInvoiceBtn');
            let machines = @json($machines);
            let stockMap = {};

            function renderStocksInfo() {
                const box = document.getElementById('stocksInfoContainer');
                const keys = Object.keys(stockMap);
                if (keys.length === 0) { box.innerHTML = ''; return; }
                let html = '';
                keys.forEach(mid => {
                    const s = stockMap[mid];
                    if (!s) return;
                    html += `<div class="flex items-center gap-6 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span class="text-sm font-semibold text-gray-700">البير:</span>
                            <span class="text-sm text-gray-800 font-bold">${s.name}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-700">النوع:</span>
                            <span class="text-sm text-gray-800">${s.type_text}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-700">الكمية:</span>
                            <span class="text-sm font-bold text-amber-700">${formatWithCommas(s.qty)}</span>
                            <span class="text-xs text-gray-500">لتر</span>
                        </div>
                    </div>`;
                });
                box.innerHTML = html;
            }

            // تحميل بيانات البير للماكينة المحددة مسبقاً
            (function() {
                let initialMachineId = '{{ $machine_detail->machine_id }}';
                if (initialMachineId) {
                    let initialLabel = $('.fuel-item:first .stock-label');
                    $.ajax({
                        url: '{{ url("/machine/stock") }}/' + initialMachineId,
                        method: 'GET',
                        success: function(data) {
                            if (data.stock) {
                                stockMap[initialMachineId] = data.stock;
                                initialLabel.text('البير: ' + data.stock.name);
                                renderStocksInfo();
                            } else {
                                initialLabel.text('بدون بير');
                            }
                        }
                    });
                }
            })();

            $('#storeForm').validate({
                rules: {
                    employee_id: {
                        required: true
                    },
                    date: {
                        required: true
                    }
                },
                messages: {
                    employee_id: {
                        required: "يجب ادخال اسم الموظف"
                    },
                    date: {
                        required: "يجب ادخال التاريخ"
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

                        const flexWrapper = element.next('.select2').parent();

                        if (flexWrapper.hasClass('flex')) {

                            error.insertAfter(flexWrapper);

                        } else {

                            error.insertAfter(element.next('.select2'));
                        }

                    } else {

                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    // منع الحفظ إذا في صفوف ما زالت بحاجة لتصحيح
                    let problemRows = [];
                    document.querySelectorAll('.fuel-item').forEach(item => {
                        if (item.dataset.needsCorrection === '1') {
                            const m = item.querySelector('.machine');
                            const name = m?.options[m.selectedIndex]?.text?.trim() || 'صف غير محدد';
                            problemRows.push(name);
                        }
                    });
                    if (problemRows.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'صفوف تحتاج تصحيحاً قبل الحفظ',
                            html: '<div style="text-align:right;direction:rtl">الماكينات: <br><b>' + problemRows.join('<br>') + '</b></div>',
                            confirmButtonText: 'حسناً'
                        });
                        return false;
                    }

                    // فحص الرصيد لكل بير على حدة
                    let stockErrors = [];
                    let stockTotals = {};
                    document.querySelectorAll('.fuel-item').forEach(item => {
                        let netVal = parseNumber(item.querySelector('.net')?.value);
                        let machineSelect = item.querySelector('.machine');
                        let machineId = machineSelect?.value;
                        let machineName = machineSelect?.options[machineSelect.selectedIndex]?.text || '';
                        if (!machineId || netVal <= 0) return;
                        let stock = stockMap[machineId];
                        if (!stock) return;
                        if (!stockTotals[machineId]) stockTotals[machineId] = 0;
                        stockTotals[machineId] += netVal;
                    });
                    Object.keys(stockTotals).forEach(mid => {
                        let stock = stockMap[mid];
                        if (!stock) return;
                        if (stockTotals[mid] > stock.qty) {
                            stockErrors.push(`${stock.name}: المطلوب ${formatWithCommas(stockTotals[mid])} لتر، المتوفر ${formatWithCommas(stock.qty)} لتر`);
                        }
                    });
                    if (stockErrors.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'الكمية تتجاوز رصيد الأبار',
                            html: '<div style="text-align:right;direction:rtl">' + stockErrors.join('<br>') + '</div>',
                            confirmButtonText: 'حسناً'
                        });
                        return false;
                    }

                    const selectors = [
                        'input.start-counter',
                        'input.end-counter',
                        'input.price',
                        'input.net',
                        'input.total'
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

            function getMaxCounter(item) {
                let machineSelect = item.querySelector('.machine');
                let mid = machineSelect?.value;
                if (mid && stockMap[mid] && stockMap[mid].max_counter) {
                    return Number(stockMap[mid].max_counter);
                }
                return 9999999;
            }

            function getUseRollover(item) {
                let machineSelect = item.querySelector('.machine');
                let mid = machineSelect?.value;
                if (mid && stockMap[mid] && stockMap[mid].use_rollover !== undefined) {
                    return !!stockMap[mid].use_rollover;
                }
                return true;
            }

            function setEndError(item, message) {
                const endInput = item.querySelector('.end-counter');
                if (!endInput) return;
                let err = item.querySelector('.end-error');
                if (!err) {
                    err = document.createElement('p');
                    err.className = 'end-error text-xs text-red-600 mt-1 font-semibold';
                    endInput.after(err);
                }
                err.textContent = message;
            }

            function clearEndError(item) {
                const err = item.querySelector('.end-error');
                if (err) err.remove();
            }

            function clearRolloverState(item) {
                item.dataset.needsCorrection = '';
                const end = item.querySelector('.end-counter');
                if (end) end.classList.remove('border-red-500', 'border-2');
                clearEndError(item);
            }

            function markDeclined(item) {
                item.dataset.needsCorrection = '1';
                const end = item.querySelector('.end-counter');
                if (end) {
                    end.classList.add('border-red-500', 'border-2');
                }
                if (item.querySelector('.net')) item.querySelector('.net').value = '';
                if (item.querySelector('.total')) item.querySelector('.total').value = '';
                updateGrandTotals();
            }

            // الحساب مع دعم التصفير: net = (max - start) + end عند حدوث تصفير
            function calculateRow(item, forceRollover) {
                let start = parseNumber(item.querySelector(".start-counter")?.value);
                let endInput = item.querySelector(".end-counter");
                let end = parseNumber(endInput?.value);
                let price = parseNumber(item.querySelector(".price")?.value);

                let rolloverFlag = item.querySelector('.is-rollover');

                if (endInput && endInput.value.trim() !== '' && !forceRollover && !item.dataset.rolloverConfirmed && end < start) {
                    // لا تحسب تلقائياً — يقرر المستخدم عبر نافذة التأكيد عند مغادرة الحقل
                    updateGrandTotals();
                    return;
                }

                let maxC = getMaxCounter(item);
                let net;
                if ((forceRollover || item.dataset.rolloverConfirmed) && end < start) {
                    net = (maxC - start) + end;
                    if (rolloverFlag) rolloverFlag.value = '1';
                } else {
                    net = Math.max(end - start, 0);
                    if (!forceRollover && rolloverFlag && !(end < start)) rolloverFlag.value = '0';
                }

                let total = net * price;

                if (item.querySelector(".net")) item.querySelector(".net").value = formatWithCommas(
                    net);
                if (item.querySelector(".total")) item.querySelector(".total").value = formatWithCommas(
                    total);

                updateGrandTotals();
            }

            // 🔹 بوابة تأكيد التصفير عند مغادرة حقل عداد النهاية
            function checkRolloverOnBlur(item) {
                const startEl = item.querySelector('.start-counter');
                const endEl = item.querySelector('.end-counter');
                if (!startEl || !endEl) return;

                const start = parseNumber(startEl.value);
                const endRaw = endEl.value.trim();
                if (endRaw === '') {
                    clearRolloverState(item);
                    delete item.dataset.rolloverConfirmed;
                    calculateRow(item);
                    return;
                }
                const end = parseNumber(endRaw);
                const maxC = getMaxCounter(item);

                if (end > maxC) {
                    Swal.fire({
                        icon: 'error',
                        title: 'قيمة العداد تتجاوز الحد الأقصى',
                        html: '<div style="direction:rtl">الحد الأقصى للعداد: <b>' + formatWithCommas(maxC) + '</b></div>',
                        confirmButtonText: 'حسناً'
                    });
                    markDeclined(item);
                    return;
                }

                if (end >= start) {
                    clearRolloverState(item);
                    delete item.dataset.rolloverConfirmed;
                    const flag = item.querySelector('.is-rollover');
                    if (flag) flag.value = '0';
                    calculateRow(item);
                    return;
                }

                if (!getUseRollover(item)) {
                    setEndError(item, 'عداد النهاية أقل من عداد البداية — تحقق من القيم المدخلة');
                    markDeclined(item);
                    return;
                }

                // تحذير إذا تجاوزت كمية التصفير الحد المسموح — ستحتاج موافقة المدير
                const rolloverNet = (maxC - start) + end;
                const allowed = stockMap[item.querySelector('.machine')?.value]?.allowed_rollover;
                const needsApprovalWarning = (allowed !== null && allowed !== undefined && rolloverNet > Number(allowed))
                    ? '<div style="color:#b45309;margin-top:8px;font-weight:bold">⚠ كمية التصفير تتجاوز الحد المسموح (' + formatWithCommas(allowed) + ' لتر) — سيتم إرسال القراءة لاعتماد المدير قبل خصمها</div>'
                    : '';

                Swal.fire({
                    title: 'عداد النهاية أقل من عداد البداية — هل حدث تصفير للعداد (دورة كاملة)؟',
                    icon: 'warning',
                    html: needsApprovalWarning,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، حدث تصفير',
                    cancelButtonText: 'لا، خطأ في الإدخال'
                }).then((result) => {
                    if (result.isConfirmed) {
                        clearRolloverState(item);
                        item.dataset.rolloverConfirmed = '1';
                        const flag = item.querySelector('.is-rollover');
                        if (flag) flag.value = '1';
                        calculateRow(item, true);
                    } else {
                        delete item.dataset.rolloverConfirmed;
                        endEl.value = '';
                        markDeclined(item);
                    }
                });
            }

            // ==========================
            // 🔹 حساب الإجماليات العامة
            // ==========================

            function updateGrandTotals() {
                let litersSum = 0,
                    totalSum = 0;

                let hasFuelData = false; // للتحقق من وجود بيانات فعلية

                // اجمع القيم من صفوف الوقود
                document.querySelectorAll('.fuel-item').forEach(item => {
                    const netVal = item.querySelector('.net')?.value.trim();
                    const totalVal = item.querySelector('.total')?.value.trim();

                    if (netVal !== '' || totalVal !== '') hasFuelData = true;

                    litersSum += parseNumber(netVal);
                    totalSum += parseNumber(totalVal);
                });

                const grandLiters = document.getElementById('grandLiters');
                const grandTotal = document.getElementById('grandTotal');

                // ✅ فقط احسب القيم لو في بيانات فعلية
                if (hasFuelData) {
                    const net = Math.max(totalSum, 0);

                    if (grandLiters) grandLiters.value = formatWithCommas(litersSum);
                    if (grandTotal) grandTotal.value = formatWithCommas(totalSum);
                } else {
                    // لو الصفحة لسه فاضية، نخلي كل القيم فارغة
                    if (grandLiters) grandLiters.value = '';
                    if (grandTotal) grandTotal.value = '';
                }

            }

            function attachInvoiceEvents(item) {
                if (!item) return;

                const start = item.querySelector('.start-counter');
                const end = item.querySelector('.end-counter');
                const price = item.querySelector('.price');

                // استخدام الفورماتر على الحقول الرقمية ذات الصلة
                if (start) attachLiveFormatter(start, () => calculateRow(item));
                if (end) {
                    attachLiveFormatter(end, () => calculateRow(item));
                    // بوابة التأكيد عند مغادرة حقل عداد النهاية
                    end.addEventListener('blur', () => checkRolloverOnBlur(item));
                }
                if (price) attachLiveFormatter(price, () => calculateRow(item));

                // زر الحذف في صف الفاتورة
                const removeBtn = item.querySelector('.remove-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        item.remove();
                        updateGrandTotals();
                    });
                }

                // لو فيه حقول رقمية إضافية داخل الصف (مثل expense/expense) سننصّب الفورماتر تلقائياً
                item.querySelectorAll('input[type="text"]').forEach(inp => {
                    const name = inp.name || '';
                    if (name.match(
                            /liters|total|net|price|start_counter|end_counter/
                        )) {
                        // attach formatter if not already attached (attachLiveFormatter can be called multiple times safely)
                        if (inp !== start && inp !== end && inp !== price) attachLiveFormatter(
                            inp, () =>
                            calculateRow(item));
                    }
                });
            }

            function generateMachineOptions() {
                let options = `<option value="">اختر الماكينة</option>`;
                machines.forEach(machine => {
                    options += `<option value="${machine.id}">${machine.name}</option>`;
                });
                return options;
            }

            // --- إضافة فاتورة (إن شاء الله الكود دا موجود سابقاً) ---
            if (addInvoiceBtn) {
                addInvoiceBtn.addEventListener('click', () => {
                    const newItem = document.createElement('div');
                    let index = document.querySelectorAll('.fuel-item').length;
                    newItem.className =
                        "fuel-item grid grid-cols-1 md:grid-cols-7 gap-4 pt-4 bg-primary-soft rounded-lg relative";
                    newItem.innerHTML = `
                        <button type="button" class="remove-btn absolute top-2 left-2 text-red-500">✖</button>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينات</label>
                            <div class="flex items-center">
                                {{-- زر الإضافة (معطل) --}}

                                <select name="machine_id[${index}]" required class="machine w-full p-2 border border-gray-300 rounded-lg select2" required>
                                    ${generateMachineOptions()}
                                </select>
                            </div>
                            <span class="stock-label block text-xs text-green-700 mt-1 font-semibold"></span>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">المسدسات</label>
                            <div class="flex items-center">
                                {{-- زر الإضافة (معطل) --}}

                                <select name="gun_id[${index}]" required class="gun w-full p-2 border border-gray-300 rounded-lg select2" required>
                                </select>
                            </div>
                        </div>

                        <div class="mr-5">
                            <label>عداد البداية</label>
                            <input type="text" name="start_counter[${index}]" class="start-counter w-full p-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label>عداد النهاية</label>
                            <input type="text" name="end_counter[${index}]" class="end-counter w-full p-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label>صافي اللتر</label>
                            <input type="hidden" name="is_rollover[${index}]" value="0" class="is-rollover">
                            <input type="text" name="net[${index}]" readonly class="net w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        <div>
                            <label>السعر</label>
                            <input type="text" name="price[${index}]" class="price w-full p-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label>الإجمالي</label>
                            <input type="text" name="total[${index}]" readonly class="total w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>
                    `;

                    container.appendChild(newItem);

                    if ($.fn.select2) {
                        $(newItem).find('select.select2, select').first().select2({
                            placeholder: 'اختر الماكينة',
                            width: '100%'
                        });

                        $(newItem).find('select.select2, select').last().select2({
                            placeholder: 'اختر المسدس',
                            width: '100%'
                        });
                    }
                    attachInvoiceEvents(newItem);
                });
            }



            document.addEventListener('click', function(e) {
                const addBtnMachine = e.target.closest('.add-machine-btn');
                const addBtnGun = e.target.closest('.add-gun-btn');
                if (addBtnMachine) {
                    currentSelectMachine = null;
                    const modal = document.getElementById('addMachineModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    currentSelectMachine = addBtnMachine.closest('div').querySelector('select.machine');
                }

                if (addBtnGun) {
                    const modal = document.getElementById('addGunModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // نخزن الـ select المرتبط بنفس السطر
                    currentSelectGun = addBtnGun.closest('div').querySelector('select.gun');
                    currentSelectMachine = addBtnGun.closest('.fuel-item').querySelector('select.machine');
                    $('#machineId').val(currentSelectMachine.value);
                }

                // إغلاق المودال
                if (e.target.id === 'closeModalBtn' || e.target.id === 'addMachineModal') {
                    const modal = document.getElementById('addMachineModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                if (e.target.id === 'closeModalBtn' || e.target.id === 'addGunModal') {
                    const modal = document.getElementById('addGunModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    currentSelectGun = null;
                }
            });

            document.querySelector('#addMachineModal > div').addEventListener('click', e => e.stopPropagation());
            document.querySelector('#addGunModal > div').addEventListener('click', e => e.stopPropagation());

            $(document).on('change', '.machine', function() {
                let machineId = $(this).val();
                let stationId = {{ $machine_detail->station_id }};
                let currentItem = $(this).closest('.fuel-item');
                let prevId = this.dataset.prevMachine || '';
                if (prevId && prevId !== machineId) delete stockMap[prevId];
                this.dataset.prevMachine = machineId;

                if (!machineId) {
                    currentItem.find('.stock-label').text('');
                    renderStocksInfo();
                    return;
                }

                $.ajax({
                    url: '{{ url("/machine/stock") }}/' + machineId,
                    method: 'GET',
                    success: function(data) {
                        if (data.stock) {
                            stockMap[machineId] = data.stock;
                            currentItem.find('.stock-label').text('البير: ' + data.stock.name);
                        } else {
                            delete stockMap[machineId];
                            currentItem.find('.stock-label').text('بدون بير');
                        }
                        renderStocksInfo();
                    }
                });

                $.ajax({
                    url: '{{ route('gun.getGun') }}',
                    method: 'GET',
                    data: {
                        machine_id: machineId,
                        station_id: stationId,
                    },
                    success: function(data) {
                        if (data.success) {

                            let container = $(this).closest(
                                '.fuel-item');
                            let select = container.find('.gun');

                            select.empty();

                            data.guns.forEach(gun => {
                                select.append(
                                    `<option value="${gun.id}">${gun.name}</option>`
                                );
                            });

                            select.trigger('change');
                        }
                    }.bind(this)
                });
            });

            $("#addMachineForm").validate({
                rules: {
                    name: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "يرجى إدخال اسم الماكينة"
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
                    const name = $("#machineName").val().trim();
                    const stationId = $("#stationId").val();

                    fetch('{{ route('machines.store.ajax') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: name,
                                station_id: stationId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                // ✅ إغلاق المودال
                                const modal = document.getElementById('addMachineModal');
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                                form.reset();

                                // ✅ إضافة الماكينة الجديدة إلى select الصحيح فقط
                                if (currentSelectMachine) {
                                    const newOption = new Option(data.machine.name, data.machine.id,
                                        true, true);
                                    $(currentSelectMachine).append(newOption).trigger('change');
                                }

                                // ✅ حفظ الماكينة في المصفوفة لتظهر في الصفوف الجديدة
                                machines.push({id: data.machine.id, name: data.machine.name});

                                // ✅ رسالة نجاح (toast في أسفل اليمين)
                                Swal.fire({
                                    toast: true,
                                    position: 'bottom-end',
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });

                                currentSelectMachine = null;
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'bottom-end',
                                    icon: 'error',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                toast: true,
                                position: 'bottom-end',
                                icon: 'error',
                                title: 'حدث خطأ أثناء حفظ البيانات، يرجى المحاولة مرة أخرى',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        });
                }
            });

            $("#addGunForm").validate({
                rules: {
                    name: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "يرجى إدخال اسم المسدس"
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
                    let name = $("#gunName").val().trim();
                    let stationId = $("#stationId").val();
                    fetch('{{ route('gun.store.ajax') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: name,
                                station_id: stationId,
                                machine_id: $('#machineId').val(),
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                // ✅ إغلاق المودال
                                const modal = document.getElementById('addGunModal');
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                                form.reset();

                                // ✅ إضافة الماكينة الجديدة إلى select الصحيح فقط
                                if (currentSelectGun) {
                                    const newOption = new Option(data.gun.name, data.gun.id,
                                        true, true);
                                    $(currentSelectGun).append(newOption).trigger('change');
                                }

                                // ✅ رسالة نجاح (toast في أسفل اليمين)
                                Swal.fire({
                                    toast: true,
                                    position: 'bottom-end',
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });

                                currentSelectGun = null;
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'bottom-end',
                                    icon: 'error',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                toast: true,
                                position: 'bottom-end',
                                icon: 'error',
                                title: 'حدث خطأ أثناء حفظ البيانات، يرجى المحاولة مرة أخرى',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        });
                }
            });


            // attach to existing fuel items on load
            container.querySelectorAll('.fuel-item').forEach(item => attachInvoiceEvents(item));

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
