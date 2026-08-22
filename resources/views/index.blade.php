@extends('layouts.app')

@section('title', 'اختيار الطرمبة')

@section('content')
    <!-- البطاقة الرئيسية -->
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="max-w-6xl w-full bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار المؤسسة"
                        class="w-28 h-28 mx-auto object-contain">
                </div>
                <h1 class="text-4xl font-bold text-gray-800">نظام تسجيل بيانات الوقود</h1>
                <p class="text-gray-600 mt-2">اختر محطة الوقود للمتابعة</p>
            </div>

            @can('stations.create')
                <div class="flex justify-start mb-6">
                    <button id="openModal"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg shadow-sm hover:bg-green-700 transition">
                        + إضافة محطة جديدة
                    </button>
                </div>
            @endcan

            <!-- شبكة Bento -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- بطاقة إحصائيات -->
                <div class="bg-gray-900 rounded-xl shadow-lg p-6 flex flex-col items-center justify-center text-center min-h-[130px]">
                    <span id="stationCount" class="text-7xl font-bold text-amber-400 leading-none">0</span>
                    <span class="text-gray-300 mt-3 text-xs font-medium opacity-60">عدد المحطات</span>
                </div>

                @foreach ($stations as $station)
                    @if (in_array($station->id, auth()->user()->stations()->pluck('station_id')->toArray()))
                        <div class="station-card relative p-5"
                            style="border-bottom: 3px solid var(--color-primary); background-color: var(--color-surface-2);">

                            <!-- زر التلاتة نقاط -->
                            <div class="flex items-center justify-between mb-5">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open"
                                        class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6h.01M12 12h.01M12 18h.01" />
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false" x-transition
                                        class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-2 text-sm">
                                            @can('stations.edit')
                                                <button @click="open = false"
                                                    class="edit-btn w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    data-id="{{ $station->id }}" data-name="{{ $station->name }}">
                                                    تعديل
                                                </button>
                                            @endcan
                                            @canany(['stocks.create', 'machines.create'])
                                                <a href="{{ route('station_setup.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    تهيئة المحطة
                                                </a>
                                            @endcanany
                                            @can('stations.delete')
                                                <form method="POST" action="{{ route('station.destroy', $station->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="delete-btn w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                                        حذف
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('tunckers.create')
                                                <a href="{{ route('tuncker.create', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    تسجيل التنكر
                                                </a>
                                            @endcan
                                            @can('tunckers.view')
                                                <a href="{{ route('tuncker.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة التناكر لدى المحطة
                                                </a>
                                            @endcan
                                            @can('clients.view')
                                                <a href="{{ route('client.station', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    ادارة الحسابات لدى المحطة
                                                </a>
                                            @endcan
                                            @can('employees.view')
                                                <a href="{{ route('employee.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة الموظفين
                                                </a>
                                            @endcan
                                            @can('stocks.view')
                                                <a href="{{ route('stock.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة الابار
                                                </a>
                                            @endcan
                                            @can('machine_details.create')
                                                <a href="{{ route('machine_detail.create', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    تسجيل العدادات
                                                </a>
                                            @endcan
                                            @can('machine_details.view')
                                                <a href="{{ route('machine_detail.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة العدادات لدى المحطة
                                                </a>
                                            @endcan
                                            @can('deposit_details.create')
                                                <a href="{{ route('deposit_detail.create', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    تسجيل التوريدات
                                                </a>
                                            @endcan
                                            @can('expenses.create')
                                                <a href="{{ route('expense.create', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    تسجيل المصروفات
                                                </a>
                                            @endcan
                                            @can('deposit_details.view')
                                                <a href="{{ route('deposit_detail.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة التوريدات لدى المحطة
                                                </a>
                                            @endcan
                                            @can('expenses.view')
                                                <a href="{{ route('expense.index', $station->id) }}"
                                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                                    قائمة المصروفات لدى المحطة
                                                </a>
                                            @endcan
                                    </div>
                                </div>
                            </div>

                            <!-- الاسم + مؤشر الحالة -->
                            <div class="mb-5">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 shrink-0 text-green-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14 6h4l2 3v9a2 2 0 0 1-2 2h-2" />
                                        <circle cx="10" cy="8" r="2" />
                                    </svg>
                                    <h2 class="text-lg font-bold text-gray-800 leading-snug">{{ $station->name }}</h2>
                                </div>
                                <span class="inline-flex items-center gap-1.5 mt-1.5 text-sm text-gray-600">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    نشطة
                                </span>
                            </div>

                            <!-- زر التسجيل -->
                            @can('tunckers.create')
                                    <a href="{{ route('tuncker.create', $station->id) }}"
                                        class="block text-center text-green-600 font-bold text-sm hover:underline transition">
                                         تسجيل التناكر ←
                                    </a>
                            @endcan
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </div>

    <!-- مودال إضافة / تعديل الطرمبة -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center" id="modalTitle">إضافة طرمبة جديدة</h2>

            <form id="stationForm" method="POST" action="{{ route('station.store') }}">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الطرمبة</label>
                    <input type="text" id="stationName" name="name"
                        class="w-full p-2 border border-gray-300 rounded-lg" required>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" id="closeModal"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">إلغاء</button>
                    <button type="submit" id="submitBtn"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openModalBtn = document.getElementById('openModal');
            const closeModalBtn = document.getElementById('closeModal');
            const modal = document.getElementById('modal');
            const stationForm = document.getElementById('stationForm');
            const modalTitle = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            const stationName = document.getElementById('stationName');

            const storeUrl = "{{ route('station.store') }}";
            const updateUrlTemplate = "{{ url('station') }}/";

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

            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
                window.addEventListener('click', function(e) {
                    if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <!-- عدّاد الطرمبات -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('stationCount');
            if (el) el.textContent = document.querySelectorAll('.station-card').length;
        });
    </script>
@endsection
