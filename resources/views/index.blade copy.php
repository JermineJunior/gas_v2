<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>اختيار الطرمبة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    @include('header')

    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="max-w-6xl w-full bg-white rounded-2xl shadow-2xl p-8">

            <!-- Header -->
            <div class="text-center mb-10">
                <img src="{{ asset('images/logo.png') }}" class="w-28 mx-auto mb-4">
                <h1 class="text-4xl font-bold text-[#2E2A6F]">
                    نظام تسجيل بيانات الوقود
                </h1>
                <p class="text-gray-500 mt-2">اختر طرمبة الوقود للمتابعة</p>
            </div>

            @if (auth()->user()->type == 0)
                <div class="flex justify-end mb-6">
                    <button id="openModal"
                        class="px-6 py-2 rounded-xl text-white font-semibold shadow
                                 bg-gradient-to-r from-[#7F1D1D] to-[#B91C1C]
                                hover:from-[#B91C1C] hover:to-[#7F1D1D] transition">
                        + إضافة طرمبة جديدة
                    </button>
                </div>
            @endif

            <!-- Stations -->
            <div class="space-y-4">

                @foreach ($stations as $station)
                    @if (in_array($station->id, auth()->user()->stations()->pluck('station_id')->toArray()))
                        <div
                            class="flex items-center justify-between bg-white rounded-2xl shadow-md
                        hover:shadow-xl transition px-6 py-4 group border border-gray-100">

                            <!-- Info -->
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-14 h-14 rounded-xl flex items-center justify-center text-white shadow
                                bg-gradient-to-br from-[#7F1D1D] to-[#B91C1C]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 6h4l2 3v9a2 2 0 0 1-2 2h-2" />
                                    </svg>
                                </div>

                                <div>
                                    <h3
                                        class="text-lg font-bold text-[#2E2A6F]
                                    group-hover:text-[#7F1D1D] transition">
                                        {{ $station->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ optional(
                                            $station->users()->whereNotNull('type')->whereNotBetween('type', [0, 1])->first(),
                                        )->name ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Menu -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-3xl text-[#7F1D1D] hover:text-[#B91C1C]">
                                    ⋮
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute left-0 mt-2 bg-white border rounded-xl shadow-lg w-52 z-50 overflow-hidden">

                                    @if (auth()->user()->type == 0 || auth()->user()->type == 1)
                                        <!-- تعديل -->
                                        <button @click="open = false"
                                            class="edit-btn block w-full text-right px-4 py-3 hover:bg-gray-100 text-sm font-semibold"
                                            data-id="{{ $station->id }}" data-name="{{ $station->name }}">
                                            ✏️ تعديل
                                        </button>

                                        <!-- حذف -->
                                        <form method="POST" action="{{ route('station.destroy', $station->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="delete-btn block w-full text-right px-4 py-3 hover:bg-red-50 text-red-600 text-sm font-semibold">
                                                🗑️ حذف
                                            </button>
                                        </form>

                                        <hr>

                                        <a href="{{ route('tuncker.create', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            تسجيل التنكر
                                        </a>

                                        <a href="{{ route('tuncker.index', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            كل التناكر التي تم شحنها
                                        </a>

                                        <a href="{{ route('client.station', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            إدارة الحسابات لدى المحطة
                                        </a>

                                        <a href="{{ route('operation.index', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            كل العمليات
                                        </a>
                                    @elseif (auth()->user()->type == 2)
                                        <button @click="open = false"
                                            class="edit-btn block w-full text-right px-4 py-3 hover:bg-gray-100 text-sm font-semibold"
                                            data-id="{{ $station->id }}" data-name="{{ $station->name }}">
                                            ✏️ تعديل
                                        </button>

                                        <!-- حذف -->
                                        <form method="POST" action="{{ route('station.destroy', $station->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="delete-btn block w-full text-right px-4 py-3 hover:bg-red-50 text-red-600 text-sm font-semibold">
                                                🗑️ حذف
                                            </button>
                                        </form>
                                        <a href="{{ route('operation.create', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            تسجيل التنكر
                                        </a>
                                        <a href="{{ route('client.station', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            إدارة الحسابات لدى المحطة
                                        </a>

                                        <a href="{{ route('operation.index', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            كل العمليات
                                        </a>
                                    @else
                                        <a href="{{ route('operation.create', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            تسجيل التنكر
                                        </a>

                                        <a href="{{ route('operation.index', $station->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 text-sm font-semibold">
                                            كل العمليات
                                        </a>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </div>
    <!-- مودال إضافة / تعديل الطرمبة -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

            <h2 id="modalTitle" class="text-2xl font-bold mb-6 text-center text-[#2E2A6F]">
                إضافة طرمبة جديدة
            </h2>

            <form id="stationForm" method="POST" action="{{ route('station.store') }}">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم الطرمبة
                    </label>
                    <input type="text" id="stationName" name="name" required
                        class="w-full p-3 border border-gray-300 rounded-xl
                    focus:outline-none focus:border-[#7F1D1D] focus:ring focus:ring-[#7F1D1D]/30">
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" id="closeModal"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        إلغاء
                    </button>

                    <button type="submit" id="submitBtn"
                        class="px-6 py-2 rounded-xl text-white font-semibold shadow
                    bg-gradient-to-r from-[#7F1D1D] to-[#B91C1C]
                    hover:from-[#B91C1C] hover:to-[#7F1D1D] transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
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


    <!-- Sweet Alert.js -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @include('messages')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openModalBtn = document.getElementById('openModal'); // زر إضافة جديد (إن وجد)
            const closeModalBtn = document.getElementById('closeModal');
            const modal = document.getElementById('modal');
            const stationForm = document.getElementById('stationForm');
            const modalTitle = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            const stationName = document.getElementById('stationName');

            const storeUrl = "{{ route('station.store') }}";
            const updateUrlTemplate = "{{ url('station') }}/";

            // فتح المودال للإضافة
            function openAddModal() {
                stationForm.action = storeUrl;
                const prevMethod = stationForm.querySelector('input[name="_method"]');
                if (prevMethod) prevMethod.remove();


                stationForm.reset();
                modalTitle.textContent = 'إضافة طرمبة جديدة';
                submitBtn.textContent = 'حفظ';
                modal.classList.remove('hidden');
            }

            if (openModalBtn) openModalBtn.addEventListener('click', openAddModal);
            if (closeModalBtn) closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('hidden');
            });

            // فتح المودال للتعديل
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {

                    let id = this.dataset.id;
                    let name = this.dataset.name;

                    stationForm.action = updateUrlTemplate + id;

                    if (!stationForm.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        stationForm.appendChild(methodInput);
                    }

                    stationName.value = name;
                    modalTitle.textContent = 'تعديل بيانات الطرمبة';
                    submitBtn.textContent = 'تحديث';

                    modal.classList.remove('hidden');
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {

                    let form = this.closest('form');

                    Swal.fire({
                        title: 'هل تريد حذف هذه المحطة؟',
                        text: "لن تتمكن من استرجاعها بعد الحذف!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#C98A2E',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذفها',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll(".menu-btn").forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    let menu = btn.parentElement.querySelector(".menu");

                    document.querySelectorAll(".menu").forEach(m => {
                        if (m !== menu) m.classList.add("hidden");
                    });

                    menu.classList.toggle("hidden");
                });
            });

            window.addEventListener("click", () => {
                document.querySelectorAll(".menu").forEach(m => m.classList.add("hidden"));
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    let form = this.closest('form'); // نحصل على الفورم التابع للزر

                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "لن تتمكن من التراجع عن هذه العملية!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذفها',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // ينفذ الحذف
                        }
                    })
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

            // مودال العمليات
            const operationsModal = document.getElementById('operationsModal');
            const closeOperationsModal = document.getElementById('closeOperationsModal');

            // أزرار فتح المودال
            document.querySelectorAll('.openOperationsModal').forEach(btn => {
                btn.addEventListener('click', () => {
                    operationsModal.classList.remove('hidden');
                });
            });

            // زر إغلاق المودال
            if (closeOperationsModal) {
                closeOperationsModal.addEventListener('click', () => {
                    operationsModal.classList.add('hidden');
                });
            }

            // إغلاق عند الضغط برة
            window.addEventListener('click', (e) => {
                if (e.target === operationsModal) {
                    operationsModal.classList.add('hidden');
                }
            });

        });
    </script>
</body>

</html>
