@extends('layouts.app')

@section('title', 'إدارة العدادات')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
@endsection

@section('content')
    <div x-data="{ openAdd: false, openEdit: false, editUser: { id: '', name: '', username: '', stations: [] } }">
        <!-- جدول التناكر التي تم شحنها -->
        <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">قائمة العدادات</h2>
            </div>

            <div class="space-y-4">
                @forelse ($machines as $date => $dayMachines)
                    <div class="border rounded-lg overflow-hidden">
                        <button type="button"
                            class="w-full bg-primary-strong text-white text-right px-4 py-3 font-semibold flex justify-between items-center"
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
                                            <th class="px-4 py-3 text-right">الموظف</th>
                                            <th class="px-4 py-3 text-right">الماكينة</th>
                                            <th class="px-4 py-3 text-right">المسدس</th>
                                            <th class="px-4 py-3 text-right">عداد البداية</th>
                                            <th class="px-4 py-3 text-right">عداد النهاية</th>
                                            <th class="px-4 py-3 text-right"> صافي العداد</th>
                                            <th class="px-4 py-3 text-right">سعر اللتر</th>
                                            <th class="px-4 py-3 text-right">اجمالي المبلغ</th>
                                            @canany(['machine_details.edit', 'machine_details.delete'])
                                                <th class="px-4 py-3 text-right">الإجراءات</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dayMachines as $machine)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-3">{{ $machine->employee->name }}</td>
                                                <td class="px-4 py-3">{{ $machine->machine->name }}</td>
                                                <td class="px-4 py-3">{{ $machine->gun->name }}</td>
                                                <td class="px-4 py-3">{{ number_format($machine->start_counter) }}</td>
                                                <td class="px-4 py-3">{{ number_format($machine->end_counter) }}</td>
                                                <td class="px-4 py-3">{{ number_format($machine->net) }}
                                                    @if($machine->approval_status === 'pending')
                                                        <span class="mr-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold" title="بانتظار موافقة المدير — لم تُخصم من البير">بانتظار الموافقة</span>
                                                    @elseif($machine->is_rollover)
                                                        <span class="mr-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold" title="حدث تصفير للعداد">تصفير</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">{{ number_format($machine->price) }}</td>
                                                <td class="px-4 py-3">{{ number_format($machine->total) }}</td>
                                                @canany(['machine_details.edit', 'machine_details.delete'])
                                                    <td class="px-4 py-3 flex gap-2">
                                                        @can('machine_details.edit')
                                                            <a href="{{ route('machine_detail.edit', $machine->id) }}"
                                                                class="bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">تعديل</a>
                                                        @endcan
                                                        @can('machine_details.delete')
                                                            <form action="{{ route('machine_detail.delete', $machine->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button"
                                                                    class="delete-btn bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">حذف</button>
                                                            </form>
                                                        @endcan
                                                    </td>
                                                @endcanany
                                            </tr>
                                        @endforeach
                                        <tr class="border-b hover:bg-gray-200">
                                            <td class="px-4 py-3 text-center" colspan="6">الاجماليات</td>
                                            <td>{{ number_format($machine->sum('net')) }}</td>
                                            <td></td>
                                            <td>{{ number_format($machine->sum('total')) }}</td>
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
    </div>
@endsection

@section('scripts')
    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
@endsection
