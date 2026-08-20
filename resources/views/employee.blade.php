<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الموظفين</title>
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
</head>

<body class="bg-gray-100 min-h-screen p-6" x-data="{ openAdd: false, openEdit: false, editemployee: { id: '', name: '', phone: '' } }">
    <!-- شريط علوي أفقي (خفيف، خارجي عن الكارد) -->
    @include('header')

    <!-- جدول المستخدمين -->
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">الموظفين المسجلين</h2>
            @can('employees.edit')
            <button @click="openAdd = true"
                class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                + إضافة موظف جديد
            </button>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead class="bg-primary-strong text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">الاسم</th>
                        <th class="px-4 py-3 text-right">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $employee->name }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                @can('employees.edit')
                                <button
                                    @click="
                                    editemployee = {id:{{ $employee->id }}, name:'{{ $employee->name }}'};
                                    openEdit = true;
                                "
                                    class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700">
                                    تعديل
                                </button>
                                @endcan
                                @can('employees.delete')
                                <form action="{{ route('employee.delete', $employee->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class=" delete-btn bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">حذف</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال إضافة -->
    <div x-show="openAdd" x-transition
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
            <button @click="openAdd = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">إضافة موظف جديد</h2>

            <form action="{{ route('employee.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="station_id" value="{{ $station->id }}">
                <div>
                    <label class="block text-gray-700 mb-1">الاسم</label>
                    <input type="text" name="name" required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال تعديل -->
    <div x-show="openEdit" x-transition
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
            <button @click="openEdit = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">تعديل موظف</h2>

            <form action="{{ route('employee.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editemployee.id">
                <div>
                    <label class="block text-gray-700 mb-1">الاسم</label>
                    <input type="text" name="name" :value="editemployee.name" required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        تحديث
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
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">كلمة السر الجديدة</label>
                        <input type="password" name="new_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">تأكيد كلمة السر</label>
                        <input type="password" name="new_password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                </div>

                <div class="flex justify-end mt-4 gap-2">
                    <button type="button" @click="show = false"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                        إغلاق
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-primary-strong text-white">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        $(document).ready(function() {

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
        });
    </script>

</body>

</html>
