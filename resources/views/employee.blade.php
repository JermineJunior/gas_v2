@extends('layouts.app')

@section('title', 'إدارة الموظفين')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div x-data="{ openAdd: false, openEdit: false, editemployee: { id: '', name: '', phone: '' } }">
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

    </div>
@endsection

@section('scripts')
    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
@endsection
