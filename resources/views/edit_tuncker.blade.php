<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>{{ $tuncker->station->name }} - تسجيل بيانات الوقود</title>
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

        <!-- Form -->
        <form action="{{ route('tuncker.update', $tuncker->id) }}" method="POST" id="storeForm">
            @csrf
            @method('PUT')
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-[#7F1D1D] to-[#B91C1C] rounded-xl flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                            <path d="M15 18H9" />
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                            <circle cx="17" cy="18" r="2" />
                            <circle cx="7" cy="18" r="2" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $tuncker->station->name }}</h1>
                        <p class="text-gray-600">تسجيل بيانات الوقود</p>
                    </div>
                </div>
            </div>

            <!-- معلومات عامة -->
            <div class="space-y-6 p-4 bg-[#FDE8E8] rounded-lg mb-6">

                <input type="hidden" name="station_id" value="{{ $tuncker->station_id }}">

                <!-- الصف الأول -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- التاريخ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ</label>
                        <input type="date" name="date"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            value="{{ old('date', $tuncker->date->format('Y-m-d')) }}">
                    </div>

                    <!-- رقم التنكر -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم التنكر</label>
                        <input type="text" name="tuncker_no"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            value="{{ old('tuncker_no', $tuncker->tuncker_no) }}">
                    </div>

                    <!-- اسم السائق -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم السائق</label>
                        <input type="text" name="driver_name"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            value="{{ old('driver_name', $tuncker->driver_name) }}">
                    </div>
                </div>

                <!-- الصف الثاني -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- نوع الوقود -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع الوقود</label>
                        <select name="fuel_type" id="fuel_type"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                            <option value="">اختر نوع الوقود</option>
                            <option value="1" {{ old('fuel_type', $tuncker->fuel_type) == 1 ? 'selected' : '' }}>
                                جازولين</option>
                            <option value="2" {{ old('fuel_type', $tuncker->fuel_type) == 2 ? 'selected' : '' }}>
                                بنزين</option>
                        </select>
                    </div>

                    <!-- كمية الوقود -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">كمية الوقود (لتر)</label>
                        <input type="text" name="fuel_quantity"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            placeholder="ادخل كمية الوقود"
                            value="{{ old('fuel_quantity', number_format($tuncker->fuel_quantity)) }}">
                    </div>

                    <!-- اسم العميل -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المورد </label>
                        <select name="supplier_id" id="supplier_id"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                            @foreach ($suppliers as $supplier)
                                <option value="">من فضلك قم باختيار المورد</option>
                                <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id', $tuncker->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">الابار</h2>
                    <button id="addDepositBtn" type="button"
                        class="flex items-center bg-[#7F1D1D] text-white px-3 py-1 rounded-lg hover:bg-[#5F1515]">
                        إضافة الوقود للبير
                    </button>
                </div>

                <div id="depositContainer" class="space-y-3">
                    @foreach ($tuncker->stockDetail as $stock)
                        <div
                            class="deposit-item grid grid-cols-{{ $loop->index != 0 ? '[1fr,1.5fr,40px]' : '[1fr,1.5fr]' }} gap-3 items-start p-4 bg-[#FDE8E8] rounded-lg relative">
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">كمية الوقود</label>
                                <input type="text" name="qty[0]" id="qty_0" placeholder="0.00" required
                                    value="{{ number_format($stock->qty) }}"
                                    class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">البير</label>
                                <select name="stock_id[0]" class="stock_id w-full" required>
                                    @foreach ($stocks as $item)
                                        <option @selected($item->id == $stock->stock_id) value="{{ $item->id }}">
                                            {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($loop->index != 0)
                                <button type="button"
                                    class="remove-deposit-row text-red-600 font-bold text-lg mt-6">✖</button>
                            @endif
                        </div>
                    @endforeach

                </div>

            </div>

            <button type="submit"
                class="flex items-center bg-[#7F1D1D] text-white px-4 py-2 rounded-lg hover:bg-[#5F1515] transition-colors">
                حفظ
            </button>
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
            $('#fuel_type').select2({
                width: '100%',
                placeholder: 'نوع الوقود'
            });

            $('#supplier_id').select2({
                width: '100%',
                placeholder: 'اسم العميل'
            });

            $('.stock_id').select2();


            // --- دوال مساعدة ---
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

            // --- عناصر الصفحة ---
            const fuelTypeSelect = document.querySelector('select[name="fuel_type"]');
            const fuelQuantityInput = document.querySelector('input[name="fuel_quantity"]');
            const storeForm = document.getElementById('storeForm');
            const depositContainer = document.getElementById('depositContainer');
            const addDepositBtn = document.getElementById('addDepositBtn');


            if (addDepositBtn) addDepositBtn.addEventListener('click', addDepositRow);

            depositContainer.querySelectorAll(':scope > .deposit-item').forEach((row, idx) => {
                attachDepositRow(row, idx);
            });

            let station_id = {{ $tuncker->station_id }};

            function loadStocksToSelect($select, type, station_id) {

                $select.html('<option value="">جاري التحميل...</option>');

                $.ajax({
                    url: '/get-stocks-by-type',
                    type: 'GET',
                    data: {
                        type: type,
                        station_id: station_id
                    },
                    success: function(data) {

                        let options = '<option value="">اختر البير</option>';

                        data.forEach(function(stock) {
                            options += `<option value="${stock.id}">${stock.name}</option>`;
                        });

                        $select.html(options).trigger('change');
                    }
                });
            }

            $('#fuel_type').on('change', function() {

                let type = $(this).val();

                $('.stock_id').each(function() {
                    loadStocksToSelect($(this), type, station_id);
                });

            });

            function addDepositRow() {
                let index = depositContainer.querySelectorAll('.deposit-item').length;
                const row = document.createElement('div');
                row.className =
                    "deposit-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 bg-[#FDE8E8] rounded-lg relative";
                row.innerHTML = `
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">كمية الوقود</label>
                        <input type="text" name="qty[${index}]" id="qty_${index}" placeholder="0.00" required
                            class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700"> البير</label>
                        <select name="stock_id[${index}]" class="stock_id w-full" required>
                        </select>
                    </div>

                    <button type="button" class="remove-deposit-row text-red-600 font-bold text-lg mt-6">✖</button>
                `;
                depositContainer.appendChild(row);
                $('.stock_id').select2({
                    width: '100%',
                });

                let type = $('#fuel_type').val();
                let station_id = {{ $tuncker->station_id }};
                let $select = $(row).find('.stock_id');

                if (type) {
                    loadStocksToSelect($select, type, station_id);
                }

                attachDepositRow(row, index);
            }

            function attachDepositRow(row, idx) {
                const amountInput = row.querySelector('.deposit-amount');
                const descInput = row.querySelector('.deposit-desc');
                const removeBtn = row.querySelector('.remove-deposit-row');

                if (amountInput && typeof attachLiveFormatter === 'function') {
                    attachLiveFormatter(amountInput);
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        row.remove();
                        updateSettlementAndRemaining && updateSettlementAndRemaining();
                    });
                }
            }

            if (addDepositBtn) addDepositBtn.addEventListener('click', addDepositRow);

            depositContainer.querySelectorAll(':scope > .deposit-item').forEach((row, idx) => {
                attachDepositRow(row, idx);
            });

            $("#storeForm").validate({
                rules: {
                    date: {
                        required: true,
                        date: true
                    },
                    tuncker_no: {
                        required: true,
                        maxlength: 255
                    },
                    driver_name: {
                        required: true,
                        maxlength: 255
                    },
                    fuel_type: {
                        required: true
                    },
                    fuel_quantity: {
                        required: true
                    },
                    supplier_id: {
                        required: true
                    }

                },
                messages: {
                    date: {
                        required: "حقل التاريخ مطلوب",
                        date: "الرجاء إدخال تاريخ صالح"
                    },
                    tuncker_no: {
                        required: "رقم التنكر مطلوب"
                    },
                    driver_name: {
                        required: "اسم السائق مطلوب"
                    },
                    fuel_type: {
                        required: "يجب تحديد نوع الوقوع"
                    },
                    fuel_quantity: {
                        required: "يجب ادخال كمية الوقود"
                    },
                    supplier_id: {
                        required: "يجب ادخال اسم المورد"
                    }
                },
                errorElement: "span",
                errorClass: "text-red-600 text-sm block mt-1", // ✅ نخليها block عشان تنزل تحت السطر
                highlight: function(element) {
                    $(element).addClass("border-red-500");
                },
                unhighlight: function(element) {
                    $(element).removeClass("border-red-500");
                },
                errorPlacement: function(error, element) {
                    if (element.hasClass("select2-hidden-accessible")) {
                        // ✅ حدد واجهة Select2
                        const select2Container = element.next('.select2');

                        // ✅ نحط الخطأ تحت الـ div الأب (الذي يحتوي الزر والـ select)
                        const flexWrapper = select2Container.closest('.flex');

                        if (flexWrapper.length) {
                            // نحط الخطأ تحت الـ div الحاوي للـ select والزر
                            error.insertAfter(flexWrapper);
                        } else {
                            error.insertAfter(select2Container);
                        }
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    const selectors = [
                        'input[name^="fuel_quantity"]',
                        'input[name^="qty"]'
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

            // --- فورماتر عام لأي input رقمي ---
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

            attachLiveFormatter(fuelQuantityInput);

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

        }); // end ready
    </script>
</body>

</html>
