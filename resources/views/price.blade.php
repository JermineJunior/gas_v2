<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تغيير اسعار اللتر</title>
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
        <form action="{{ route('price.store') }}" method="POST" id="storeForm">
            @csrf
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
                        <p class="text-gray-600">تغيير اسعار اللتر</p>
                    </div>
                </div>
            </div>

            <!-- معلومات عامة -->
            <div class="space-y-6 p-4 bg-[#FDE8E8] rounded-lg mb-6">


                <!-- الصف الأول -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">سعر اللتر للعملاء</label>
                        <input type="text" name="price_customer"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            value="{{ old('price_customer',auth()->user()->price_customer) }}">
                    </div>

                    <!-- اسم السائق -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">سعر اللتر للبص</label>
                        <input type="text" name="price_bus"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C]"
                            value="{{ old('price_bus',auth()->user()->price_bus) }}">
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    @include('messages')
    <script>
        $(document).ready(function() {

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

            

            // --- عناصر الصفحة ---
            const priceCustomerInput = document.querySelector('input[name="price_customer"]');
            const priceBusInput = document.querySelector('input[name="price_bus"]');
            const storeForm = document.getElementById('storeForm');

            $("#storeForm").validate({
                rules: {
                    price_customer: {
                        required: true,
                    },
                    price_bus: {
                        required: true,
                    }

                },
                messages: {
                    price_customer: {
                        required: "يجب ادخال سعر اللتر بالنسبة للعملاء"
                    },
                    price_bus: {
                        required: "يجب ادخال سعر اللتر بالنسبة للباصات"
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

            attachLiveFormatter(priceCustomerInput);
            attachLiveFormatter(priceBusInput);


            // --- تنظيف كل الأرقام (إزالة الفواصل) قبل إرسال الفورم ---
            if (storeForm) {
                storeForm.addEventListener('submit', function(e) {
                    // حدد الحقول الرقمية التي نريد تنظيفها
                    const selectors = [
                        'input[name^="price_customer"]',
                        'input[name^="price_bus"]'
                    ].join(',');

                    this.querySelectorAll(selectors).forEach(el => {
                        if (el && el.value) el.value = String(el.value).replace(/,/g,
                            '');
                    });

                    // تأكد تنظيف الحقول العامة النصية التي قد تحتوي على فواصل لا تريد إزالتها؟ (لا نفعل هنا)
                    // فورم هير يرسل للقيم النقية بدون فواصل
                });
            }

            const form = document.getElementById('storeForm');
            const submitBtn = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                // ✅ عطل الزر مباشرة
                submitBtn.disabled = true;

                // ✅ غيّر شكله ونصه
                submitBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    جاري حفظ البيانات...
                `;

                // ✅ منع إرسال الفورم أكتر من مرة
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                });
            });

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
