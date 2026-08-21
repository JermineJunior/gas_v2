<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>تفاصيل ايردادات العميل — {{ $client->name }}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">



    <style>
        /* ظبط صغير لمساحة المحتوى */
        body {
            font-family: 'Cairo', sans-serif;
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

<body class="bg-gray-100 min-h-screen p-6">
    @include('header')

    <div class="max-w-6xl mx-auto p-6">
        <!-- Card -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <!-- header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">تقاصيل العميل</h1>
                    <p class="text-sm text-gray-500">هنا تعرض كل الايرادات المتعلقة بالعميل</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- إضافة (لون شعار الفجر #00AEEF) -->
                    @can('revenue.create')
                        <button onclick="openAddModal()"
                            class="flex items-center gap-2 bg-primary-strong hover:bg-primary-strong text-white px-4 py-2 rounded-lg shadow">
                            + إضافة ايراد
                        </button>
                    @endcan
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Client info (على يسار الكارد في الصورة، هنا مبسط) -->
                <div class="mb-6 bg-primary-soft border border-primary-strong/30 rounded-lg p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <div class="text-lg font-semibold">اسم العميل: <span
                                    class="text-gray-700">{{ $client->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الجدول -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] table-auto border-collapse">
                        <thead>
                            <tr class="bg-primary-strong text-white">
                                <th class="px-4 py-3 text-right">#</th>
                                <th class="px-4 py-3 text-right">التاريخ</th>
                                <th class="px-4 py-3 text-right">الايراد</th>
                                <th class="px-4 py-3 text-right">التفاصيل</th>
                                @canany(['revenue.edit', 'revenue.delete'])
                                    <th class="px-4 py-3 text-center">الإجراءات</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody id="accountsBody">
                            @forelse ($client->details as $cus)
                                <tr class="border-b bg-gray-50 hover:bg-gray-100">
                                    <td class="px-4 py-4 text-right">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4 text-right">{{ $cus->date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-4 text-right">{{ formatNumber($cus->amount) }}</td>
                                    <td class="px-4 py-4 text-right">{{ $cus->note }}</td>
                                    @canany(['revenue.edit', 'revenue.delete'])
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex gap-2">
                                                @can('revenue.edit')
                                                    <button
                                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg edit-btn"
                                                        data-id="{{ $cus->id }}"
                                                        data-date="{{ $cus->date->format('Y-m-d') }}"
                                                        data-amount="{{ $cus->amount }}" data-note="{{ $cus->note }}"
                                                        data-route="{{ route('revenue.update', $cus->id) }}">
                                                        تعديل
                                                    </button>
                                                @endcan

                                                @can('revenue.delete')
                                                    <form action="{{ route('revenue.delete', $cus->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="delete-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg">
                                                            حذف
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td class="px-4 py-4" colspan="7">لا توجد عمليات حاليا لدى هذا العميل</td>
                                </tr>
                            @endforelse
                            <tr>
                                <td colspan="2" class="px-4 py-4 text-center font-bold">الاجمالي</td>
                                <td class="px-4 py-4 text-primary-strong font-bold">
                                    {{ number_format($client->details()->sum('amount')) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال إضافة -->
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-primary-strong">إضافة ايراد جديد</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">✖</button>
            </div>

            <form action="{{ route('revenue.store') }}" method="POST" id="storeForm">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">التاريخ</label>
                        <input name="date" type="date" required class="w-full border rounded-lg px-3 py-2" value="{{ now()->toDateString() }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">المبلغ</label>
                        <input name="amount" type="text" id="add_amount"
                            class="w-full border  rounded-lg px-3 py-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">التفاصيل</label>
                        <textarea name="note" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 bg-gray-300 rounded-lg">إلغاء</button>
                    <button type="submit" id="addSubmitBtn"
                        class="px-4 py-2 bg-primary-strong text-white rounded-lg">حفظ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال تعديل -->
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-primary-strong">تعديل ايراد</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">✖</button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">التاريخ</label>
                        <input id="edit_date" name="date" type="date" required
                            class="w-full border rounded-lg px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">المبلغ</label>
                        <input id="edit_amount" name="amount" type="text"
                            class="w-full border  rounded-lg px-3 py-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">التفاصيل</label>
                        <textarea id="edit_note" name="note" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-300 rounded-lg">إلغاء</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">تحديث</button>
                </div>
            </form>
        </div>
    </div>
    @include('messages')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        $(document).ready(function() {

            // 🔹 تنسيق الأرقام أثناء الكتابة (إضافة فاصلة كل 3 خانات)
            function formatWithCommas(numStr) {
                if (!numStr) return '';
                let s = String(numStr).replace(/,/g, '');
                if (s === '') return '';
                let parts = s.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
            }

            // 🔹 إزالة أي فواصل قبل المعالجة
            function parseNumber(str) {
                let cleaned = String(str || '').replace(/,/g, '');
                let n = parseFloat(cleaned);
                return isNaN(n) ? 0 : n;
            }


            // 🔹 إضافة الفاصلة أثناء الكتابة في اللتر والسعر
            $('#add_amount, #edit_amount').on('input', function() {
                this.value = formatWithCommas(this.value);
            });

            // 🔹 عند الضغط على تعديل
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.edit-btn');
                if (!btn) return;
                const data = {
                    id: btn.dataset.id,
                    date: btn.dataset.date,
                    amount: btn.dataset.amount,
                    note: btn.dataset.note,
                    route: btn.dataset.route,
                };
                openEditModalWithData(data);
            });

            // 🔹 تعبئة مودال التعديل
            function openEditModalWithData(data) {
                $('#edit_id').val(data.id);
                $('#edit_date').val(data.date);
                $('#edit_amount').val(formatWithCommas(data.amount));
                $('#edit_note').val(data.note || '');
                $('#editForm').attr('action', data.route);
                $('#editModal').removeClass('hidden');
            }

            // 🔹 عند الإرسال (حذف الفواصل قبل الإرسال)
            $('#storeForm, #editForm').on('submit', function() {
                $(this).find('input[type="text"]').each(function() {
                    this.value = this.value.replace(/,/g, '');
                });
            });

            // 🔹 فتح وإغلاق المودالات
            window.openAddModal = () => $('#addModal').removeClass('hidden');
            window.closeAddModal = () => $('#addModal').addClass('hidden');
            window.closeEditModal = () => $('#editModal').addClass('hidden');

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

            // 🔹 قائمة الموبايل
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

            $("#storeForm").on("submit", function(e) {

                let btn = document.getElementById("addSubmitBtn");

                // تعطيل الزر
                btn.disabled = true;

                // تغيير شكل الزر + إظهار اللودنج
                btn.innerHTML = `
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                        class="w-5 h-5 inline-block mr-2 animate-spin" 
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" 
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    جاري الحفظ...
                                `;

            });
        });
    </script>


</body>

</html>
