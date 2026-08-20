<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>{{ $tuncker->station->name }} - عرض بيانات تنكر</title>
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

        <!-- Header -->
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
                    <p class="text-gray-600">عرض بيانات تنكر</p>
                </div>
            </div>
        </div>

        <form action="{{ route('tuncker.update', $tuncker->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $tuncker->station_id }}" name="station_id">
            <!-- معلومات عامة -->
            <div class="space-y-6 p-4 bg-[#FDE8E8] rounded-lg mb-6">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- نوع الوقود -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع الوقود</label>
                        <select name="fuel_type" id="fuel_type"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]">
                            <option value="">اختر نوع الوقود</option>
                            <option value="1" @selected($tuncker->fuel_type == 1)
                                {{ old('fuel_type') == 1 ? 'selected' : '' }}>جازولين</option>
                            <option value="2" @selected($tuncker->fuel_type == 2)
                                {{ old('fuel_type') == 2 ? 'selected' : '' }}>بنزين</option>
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

                    <!-- سعر الشراء -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">سعر الشراء </label>
                        <input type="text" name="price"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            placeholder="ادخل سعر الشراء"
                            value="{{ old('depot_no', number_format($tuncker->price)) }}">
                    </div>

                    <!-- ترحيل الوقود  -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">سعر ترحيل الوقود</label>
                        <input type="text" name="transfer_cost"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            placeholder="ادخل سعر ترحيل الوقود"
                            value="{{ old('transfer_cost', number_format($tuncker->transfer_cost)) }}">
                    </div>
                </div>
            </div>
            <div class="flex justify-start space-x-3 space-x-reverse mt-6">
                <!-- زر حفظ -->
                <button type="submit" name="action" value="save"
                    class="flex items-center bg-[#4A0F18] text-white px-6 py-2 rounded-lg hover:bg-[#7F1D1D] transition-colors">
                    تعديل
                </button>
            </div>
        </form>
    </div>
    <!-- مودال الملف التعريفي -->
    <div x-data="{ show: false }" x-on:open-modal.window="if($event.detail.id === 'profileModal') show = true"
        x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-transition>

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
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    <script>
        $(document).ready(function() {
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

            function formatNumber(num) {
                if (num === null || isNaN(num)) return '';
                return Number(num).toLocaleString('en-US', {
                    maximumFractionDigits: 2
                });
            }

            // --- عناصر الصفحة ---
            const dateInput = document.querySelector('input[name="date"]');
            const tunckerNoInput = document.querySelector('input[name="tuncker_no"]');
            const driverNameInput = document.querySelector('input[name="driver_name"]');
            const fuelTypeSelect = document.querySelector('select[name="fuel_type"]');
            const fuelQuantityInput = document.querySelector('input[name="fuel_quantity"]');
            const priceInput = document.querySelector('input[name="price"]');
            const transferCostInput = document.querySelector('input[name="transfer_cost"]');

            // --- فورماتر ---
            function attachLiveFormatter(input, onChange) {
                if (!input) return;

                function update() {
                    let raw = cleanNumber(input.value);
                    input.value = formatWithCommas(raw);
                    if (typeof onChange === 'function') onChange();
                }
                input.addEventListener('input', update);
                update();
            }

            attachLiveFormatter(fuelQuantityInput);
            attachLiveFormatter(priceInput);

            $('select').select2({
                width: '100%',
                placeholder: 'اختر نوع الوقود'
            });

            function cleanNumbersBeforeSubmit() {
                // الحقول المعروفة من dd
                const fields = [
                    "fuel_quantity",
                    "price",
                    "transfer_cost"
                ];

                fields.forEach(name => {
                    let el = document.querySelector(`[name="${name}"]`);
                    if (el && el.value) {
                        el.value = el.value.replace(/,/g, ''); // إزالة الفواصل
                    }
                });

                document.querySelectorAll('input[name^="expense_amount"]').forEach(el => {
                    if (el.value) el.value = el.value.replace(/,/g, '');
                });
            }

            // اربطها مع الفورم قبل الإرسال
            document.getElementById('editForm').addEventListener('submit', function() {
                cleanNumbersBeforeSubmit();
            });
        });
    </script>


</body>

</html>
