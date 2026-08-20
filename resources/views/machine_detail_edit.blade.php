<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>{{ $machine_detail->station->name }} - تعديل عدادات</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- استدعاء خط عربي (Cairo) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .text-danger {
            color: red !important;
            font-size: 0.875rem;
            /* نفس حجم النص الصغير */
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

</head>

<body class="bg-gray-100 p-6">

    <!-- شريط علوي أفقي (خفيف، خارجي عن الكارد) -->
    @include('header')

    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        <form action="{{ route('machine_detail.update', $machine_detail->id) }}" method="POST" id="storeForm">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $machine_detail->station_id }}" name="station_id">

            <div class="mb-6">
                <div class="flex justify-end items-center mb-4"> {{-- <h2 class="text-xl font-semibold text-gray-800">تفاصيل البيع </h2> --}}
                    <button type="button" id="addFuelInvoiceBtn"
                        class="flex items-center bg-[#7F1D1D] text-white px-3 py-2 rounded-lg hover:bg-[#5F1515]">
                        إضافة عداد جديد
                    </button>
                </div>

                <div id="fuelInvoicesContainer" class="space-y-4">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-[#FDE8E8] rounded-lg relative">
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
                                class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                                value="{{ $machine_detail->date->format('Y-m-d') }}">
                        </div>

                    </div>
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-7 gap-4 bg-[#FDE8E8] rounded-lg relative">

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينة</label>
                            <div class="flex items-center gap-2">
                                <!-- زر الإضافة -->
                                <button type="button"
                                    class="add-machine-btn flex items-center justify-center bg-[#7F1D1D] text-white rounded-lg p-2 hover:bg-[#5F1515] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>

                                <select name="machine_id[0]"
                                    class="machine w-full p-2 border border-gray-300 rounded-lg select2" required>
                                    <option value="">اختر العداد</option>
                                    @foreach ($machines as $machine)
                                        <option @selected($machine_detail->machine_id == $machine->id) value="{{ $machine->id }}">
                                            {{ $machine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">المسدسات</label>
                            <div class="flex items-center">
                                <!-- زر الإضافة -->
                                <button type="button"
                                    class="add-gun-btn flex items-center justify-center bg-[#7F1D1D] text-white rounded-lg p-2 hover:bg-[#5F1515] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>

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
                                class="start-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد النهاية</label>
                            <input type="text" name="end_counter[0]" placeholder="0"
                                value="{{ number_format($machine_detail->end_counter) }}"
                                class="end-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                        </div>

                        <!-- الصافي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">صافي اللتر</label>
                            <input type="text" name="net[0]" readonly
                                value="{{ number_format($machine_detail->net) }}"
                                class="net w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        <!-- السعر -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">سعر اللتر</label>
                            <input type="text" name="price[0]" placeholder="0.00"
                                value="{{ number_format($machine_detail->price) }}"
                                class="price w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
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
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-[#FDE8E8] rounded-lg">
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
                    class="flex items-center bg-[#4A0F18] text-white px-6 py-2 rounded-lg hover:bg-[#7F1D1D] transition-colors">
                    حفظ
                </button>
            </div>

        </form>
    </div>
    <!-- مودال الملف التعريفي -->
    <div x-data="{ show: false }" x-on:open-modal.window="if($event.detail.id === 'profileModal') show = true"
        x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-transition>

        <div @click.away="show = false" class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 space-y-4">

            <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">الملف التعريفي</h2>

            <!-- اسم المستخدم -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-1">اسم المستخدم</label>
                <input type="text" value="{{ auth()->user()->name }}" readonly
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>

            <!-- نموذج تغيير كلمة السر -->
            <form action="{{ route('user.update-password') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">كلمة السر القديمة</label>
                        <input type="password" name="old_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#B91C1C] focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">كلمة السر الجديدة</label>
                        <input type="password" name="new_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#B91C1C] focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">تأكيد كلمة السر</label>
                        <input type="password" name="new_password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#B91C1C] focus:ring">
                    </div>
                </div>

                <div class="flex justify-end mt-4 gap-2">
                    <button type="button" @click="show = false"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                        إغلاق
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#7A1E2C] hover:bg-[#4A0F18] text-white">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
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
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                </div>

                <!-- العملية (مخفي) -->
                <input type="hidden" name="station_id" id="stationId"
                    value="{{ $machine_detail->station_id ?? '' }}">

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="bg-[#7F1D1D] text-white px-4 py-2 rounded-lg hover:bg-[#5F1515] transition">
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
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                </div>

                <!-- العملية (مخفي) -->
                <input type="hidden" name="station_id" id="stationId"
                    value="{{ $machine_details->station_id ?? '' }}">
                <input type="hidden" name="machine_id" id="machineId"
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="bg-[#7F1D1D] text-white px-4 py-2 rounded-lg hover:bg-[#5F1515] transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    @include('messages')
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

            function calculateRow(item) {
                let start = parseNumber(item.querySelector(".start-counter")?.value);
                let end = parseNumber(item.querySelector(".end-counter")?.value);
                let price = parseNumber(item.querySelector(".price")?.value);

                let net = Math.max(end - start, 0);
                let total = net * price;

                if (item.querySelector(".net")) item.querySelector(".net").value = formatWithCommas(
                    net);
                if (item.querySelector(".total")) item.querySelector(".total").value = formatWithCommas(
                    total);

                updateGrandTotals();
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
                if (end) attachLiveFormatter(end, () => calculateRow(item));
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
                        "fuel-item grid grid-cols-1 md:grid-cols-7 gap-4 pt-4 bg-[#FDE8E8] rounded-lg relative";
                    newItem.innerHTML = `
                        <button type="button" class="remove-btn absolute top-2 left-2 text-red-500">✖</button>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينات</label>
                            <div class="flex items-center">
                                <!-- زر الإضافة بجانب select -->
                                <button type="button"
                                    class="add-machine-btn flex items-center justify-center bg-[#7F1D1D] text-white rounded-lg p-2 hover:bg-[#5F1515] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                                
                                <select name="machine_id[${index}]" required class="machine w-full p-2 border border-gray-300 rounded-lg select2" required>
                                    ${generateMachineOptions()}
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">المسدسات</label>
                            <div class="flex items-center">
                                <!-- زر الإضافة بجانب select -->
                                <button type="button"
                                    class="add-gun-btn flex items-center justify-center bg-[#7F1D1D] text-white rounded-lg p-2 hover:bg-[#5F1515] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                                
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


</body>

</html>
