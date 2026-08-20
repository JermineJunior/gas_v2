<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التوريدات </title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- jQuery + Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- استدعاء خط عربي (Cairo) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>

    <style>
        /* تنسيق Select2 مع Tailwind */
        .select2-container .select2-selection--multiple {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.25rem;
            min-height: 42px;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice {
            background-color: #2563eb;
            border: none;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            margin-top: 0.25rem;
        }

        /* نخفي زر الإزالة (❌) */
        .select2-selection__choice__remove {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-6" x-data="{ openAdd: false, openEdit: false, editUser: { id: '', name: '', username: '', stations: [] } }">
    <!-- شريط علوي أفقي (خفيف، خارجي عن الكارد) -->
    @include('header')

    <!-- جدول التناكر التي تم شحنها -->
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">قائمة التوريدات</h2>
        </div>

        <div class="space-y-4">
            @forelse ($deposits as $date => $dayDeposits)
                <div class="border rounded-lg overflow-hidden">
                    <button type="button"
                        class="w-full bg-[#4A0F18] text-white text-right px-4 py-3 font-semibold flex justify-between items-center"
                        onclick="toggleCollapse('{{ $date }}')">
                        <span>{{ $date }}</span>
                        <svg id="icon-{{ $date }}" xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div id="collapse-{{ $date }}" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-right">#</th>
                                        <th class="px-4 py-3 text-right"> الموظف</th>
                                        <th class="px-4 py-3 text-right"> البيان</th>
                                        <th class="px-4 py-3 text-right">المبلغ</th>
                                        @if (auth()->user()->type == 3 || (auth()->user()->type == 2 && $station->id == 1))
                                            <th class="px-4 py-3 text-right">الإجراءات</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $index = 0;
                                    @endphp
                                    @foreach ($dayDeposits as $depositItem)
                                    @php
                                        $index++;
                                        $total = 0
                                    @endphp
                                        @foreach ($depositItem->deposit_details as $deposit)
                                        @php
                                            $total += $deposit->deposit_amount;
                                        @endphp
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-4 py-3">{{ $index }}</td>
                                                <td class="px-4 py-3">{{ $depositItem->employee->name ?? '-' }}</td>
                                                <td class="px-4 py-3">{{ $deposit->deposit_desc }}</td>
                                                <td class="px-4 py-3">{{ number_format($deposit->deposit_amount) }}
                                                </td>
                                                @if (auth()->user()->type == 3 || (auth()->user()->type == 2 && $station->id == 1))
                                                    <td class="px-4 py-3 flex gap-2">
                                                        <a href="{{ route('deposit_detail.edit', $depositItem->id) }}"
                                                            class="bg-[#4A0F18] text-white px-3 py-1 rounded-lg hover:bg-[#B91C1C]">تعديل</a>
                                                        <form action="{{ route('deposit_detail.delete', $depositItem->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="delete-btn bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">حذف</button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    <tr class="border-b hover:bg-gray-200">
                                        <td class="px-4 py-3 text-center" colspan="3">الاجماليات</td>
                                        <td>{{ number_format($total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-4 mt-4">
                    لا توجد أي عمليات متاحة حاليًا.
                </div>
            @endforelse
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        function toggleCollapse(id) {
            const content = document.getElementById(`collapse-${id}`);
            const icon = document.getElementById(`icon-${id}`);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
        $(document).ready(function() {
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
        });
    </script>

</body>

</html>
