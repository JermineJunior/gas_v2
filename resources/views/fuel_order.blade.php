@extends('layouts.app')

@section('title', 'طلب وقود')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
<div x-data="{ openAdd: false, openEdit: false, editsupplier: { id: '', date: '', quantity: '' } }">
    <!-- جدول المستخدمين -->
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">طلب وقود</h2>
            @can('fuel_orders.create')
                <button @click="openAdd = true"
                    class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    + إضافة طلب جديد
                </button>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead class="bg-primary-strong text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">كمية الوقود المطلوبة</th>
                        <th class="px-4 py-3 text-right">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fuel_orders as $fuel_order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $fuel_order->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ number_format($fuel_order->quantity) }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                @can('fuel_orders.edit')
                                    <button
                                        @click="
                                        editsupplier = {id:{{ $fuel_order->id }}, quantity:'{{ number_format($fuel_order->quantity) }}', date:'{{ $fuel_order->date }}'};
                                        openEdit = true;
                                    "
                                        class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700">
                                        تعديل
                                    </button>
                                @endcan
                                @can('fuel_orders.delete')
                                    <form action="{{ route('fuel_order.delete', $fuel_order->id) }}" method="POST">
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
            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">إضافة طلب وقود جديد</h2>

            <form id="FormStore" action="{{ route('fuel_order.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                <div>
                    <label class="block text-gray-700 mb-1">التاريخ</label>
                    <input type="date" name="date" required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-strong focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">الكمية</label>
                    <input type="text" name="quantity" required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-strong focus:outline-none">
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
            <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">تعديل مستخدم</h2>

            <form id="FormEdit" action="{{ route('fuel_order.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editsupplier.id">
                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                <div>
                    <label class="block text-gray-700 mb-1">التاريخ</label>
                    <input type="text" name="date" :value="editsupplier.date" required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-strong focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1"> الكمية</label>
                    <input type="text" name="quantity" :value="editsupplier.quantity"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-strong focus:outline-none">
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- form_validation scripts -->
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    <script src="{{ URL::asset('form_validation/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/additional-methods.min.js') }}"></script>
    <script src="{{ URL::asset('form_validation/messages_ar.js') }}"></script>
    <!-- jQuery (needed for $(document).ready and Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Alpine.js -->
    <!-- Select2 JS -->
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    @include('messages')
    <script>
        $(document).ready(function() {
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

            document.querySelectorAll('input[name="quantity"]').forEach(function(input) {
                attachLiveFormatter(input);
            });

            $("#FormStore, #FormEdit").validate({
                rules: {
                    date: {
                        required: true,
                        date: true
                    },
                    quantity: {
                        required: true,
                    },
                },
                messages: {
                    date: {
                        required: "حقل التاريخ مطلوب",
                        date: "الرجاء إدخال تاريخ صالح"
                    },
                    quantity: {
                        required: "يجب ادخال الكمية"
                    },

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
