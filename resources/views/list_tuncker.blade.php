@extends('layouts.app')

@section('title', 'إدارة التناكر التي تم شحنها')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
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
                <h2 class="text-2xl font-bold text-gray-800">قائمة التناكر التي تم شحنها</h2>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-full pl-5 pr-2 py-1.5">
                        <span
                            class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold">ج</span>
                        <div class="leading-tight">
                            <p class="text-[11px] text-gray-500">جازولين</p>
                            <p class="text-sm font-bold text-green-700">{{ number_format($total_gasoline) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-sky-50 border border-sky-200 rounded-full pl-5 pr-2 py-1.5">
                        <span
                            class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center text-sm font-bold">ب</span>
                        <div class="leading-tight">
                            <p class="text-[11px] text-gray-500">بنزين</p>
                            <p class="text-sm font-bold text-sky-700">{{ number_format($total_benzine) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-accent-soft border border-yellow-300 rounded-full pl-5 pr-2 py-1.5">
                        <span
                            class="w-8 h-8 rounded-full bg-accent-strong text-white flex items-center justify-center text-sm font-bold">Σ</span>
                        <div class="leading-tight">
                            <p class="text-[11px] text-gray-500">المجموع الكلي</p>
                            <p class="text-sm font-bold text-accent-strong">{{ number_format($total_amount) }}</p>
                        </div>
                    </div>
                    @canany(['tunckers.view', 'tunckers.edit'])
                        <a href="{{ route('tuncker.unmatched') }}"
                            class="flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-full hover:bg-amber-600 transition text-sm font-semibold">
                            غير مرتبطة بطلبية
                        </a>
                    @endcanany
                </div>
            </div>

            @php
                use Carbon\Carbon;
                $months = [
                    '01' => 'يناير',
                    '02' => 'فبراير',
                    '03' => 'مارس',
                    '04' => 'أبريل',
                    '05' => 'مايو',
                    '06' => 'يونيو',
                    '07' => 'يوليو',
                    '08' => 'أغسطس',
                    '09' => 'سبتمبر',
                    '10' => 'أكتوبر',
                    '11' => 'نوفمبر',
                    '12' => 'ديسمبر',
                ];
            @endphp

            <div class="space-y-4">
                @forelse ($tunckers as $monthKey => $monthtunckers)
                    @php
                        $year = Carbon::parse($monthtunckers->first()->date)->year;
                        $monthNumber = Carbon::parse($monthtunckers->first()->date)->format('m');
                        $monthName = $months[$monthNumber];
                    @endphp

                    <div class="border rounded-lg overflow-hidden">
                        <button type="button"
                            class="w-full bg-primary-strong text-white text-right px-4 py-3 font-semibold flex justify-between items-center"
                            onclick="toggleCollapse('{{ $monthKey }}')">
                            <span>{{ $monthName }} {{ $year }}</span>
                            <svg id="icon-{{ $monthKey }}" xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div id="collapse-{{ $monthKey }}" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-right">#</th>
                                            <th class="px-4 py-3 text-right">التاريخ</th>
                                            <th class="px-4 py-3 text-right">رقم التنكر</th>
                                            <th class="px-4 py-3 text-right">اسم السائق</th>
                                            <th class="px-4 py-3 text-right">نوع الوقود</th>
                                            <th class="px-4 py-3 text-right">كمية الوقود</th>
                                            <th class="px-4 py-3 text-right">اسم المورد</th>
                                            @canany(['tunckers.view', 'tunckers.edit', 'tunckers.delete'])
                                                <th class="px-4 py-3 text-right">الاجراءات</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($monthtunckers as $tuncker)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-3">{{ $tuncker->date->format('Y/m/d') }}</td>
                                                <td class="px-4 py-3">{{ $tuncker->tuncker_no }}</td>
                                                <td class="px-4 py-3">{{ $tuncker->driver_name }}</td>
                                                <td class="px-4 py-3">{{ $tuncker->fuel_type == 1 ? 'جازولين' : 'بنزين' }}
                                                    @if (empty($tuncker->fuelDelivery?->fuel_order_id))
                                                        <span
                                                            class="inline-block mr-1 bg-amber-100 text-amber-700 border border-amber-300 rounded-full px-2 py-0.5 text-[11px] font-semibold">بدون طلبية</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">{{ number_format($tuncker->fuel_quantity) }}</td>
                                                <td class="px-4 py-3">{{ $tuncker->supplier->name }}</td>
                                                @canany(['tunckers.view', 'tunckers.edit', 'tunckers.delete'])
                                                    <td class="px-4 py-3 flex gap-2">
                                                        @can('tunckers.view')
                                                            <a href="{{ route('tuncker.show', $tuncker->id) }}"
                                                                class="bg-sky-600 text-white px-3 py-1 rounded-lg hover:bg-sky-700">عرض</a>
                                                        @endcan
                                                        @can('tunckers.edit')
                                                            <a href="{{ route('tuncker.edit', $tuncker->id) }}"
                                                                class="bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">تعديل</a>

                                                            @if (empty($tuncker->fuelDelivery?->fuel_order_id) && $tuncker->fuelDelivery)
                                                                <button type="button"
                                                                    class="attach-btn bg-amber-500 text-white px-3 py-1 rounded-lg hover:bg-amber-600"
                                                                    data-delivery-id="{{ $tuncker->fuelDelivery->id }}"
                                                                    data-supplier-id="{{ $tuncker->supplier_id }}">ربط
                                                                    طلبية</button>
                                                            @endif
                                                        @endcan
                                                        @can('tunckers.delete')
                                                            <form action="{{ route('tuncker.delete', $tuncker->id) }}"
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

    <!-- مودال ربط التوريدة بأمر توريد -->
    <div id="attachModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">ربط التوريدة بأمر توريد</h3>
                <button type="button" onclick="closeAttachModal()"
                    class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-gray-600 mb-4">اختر أمر التوريد التابع لنفس مورد هذه التوريدة لربطها.</p>
            <form id="attachForm" method="POST" action="/">
                @csrf
                <input type="hidden" name="delivery_id" id="attachDeliveryId">
                <select name="fuel_order_id" id="attachFuelOrderId"
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="">جاري التحميل...</option>
                </select>
                <p id="attachNoOrders" class="hidden text-xs text-amber-600 mt-2">لا توجد أوامر توريد متاحة لهذا المورد
                    — اختر موردًا آخر أو سجّل أمر توريد جديدًا</p>
                <div class="flex gap-3 pt-5">
                    <button type="submit"
                        class="bg-primary-strong text-white px-4 py-2 rounded-lg hover:bg-primary-strong transition-colors">ربط</button>
                    <button type="button" onclick="closeAttachModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js -->

    <!-- jQuery + Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>

    <script>
        function toggleCollapse(id) {
            const content = document.getElementById(`collapse-${id}`);
            const icon = document.getElementById(`icon-${id}`);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        function closeAttachModal() {
            document.getElementById('attachModal').classList.add('hidden');
        }

        function formatNumber(num) {
            if (num === null || num === '' || isNaN(Number(num))) return '';
            return Number(num).toLocaleString('en-US', {
                maximumFractionDigits: 2
            });
        }

        function loadAttachOrders(supplierId) {
            const $sel = $('#attachFuelOrderId');
            $('#attachNoOrders').addClass('hidden');
            if (!supplierId) {
                $sel.html('<option value="">اختر المورد أولاً</option>');
                return;
            }
            $sel.html('<option value="">جاري التحميل...</option>');
            $.ajax({
                url: '/api/fuel-orders',
                type: 'GET',
                data: {
                    supplier_id: supplierId
                },
                success: function(orders) {
                    if (!orders || !orders.length) {
                        $sel.html('<option value="">لا توجد أوامر توريد متاحة</option>');
                        $('#attachNoOrders').removeClass('hidden');
                        return;
                    }
                    let options = '<option value="">اختر أمر التوريد</option>';
                    orders.forEach(function(o) {
                        options += `<option value="${o.id}">#${o.id} — ${o.date} — ${o.fuel_type_text} — متبقي ${formatNumber(o.remaining_quantity)} لتر</option>`;
                    });
                    $sel.html(options);
                }
            });
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

            // ربط توريدة غير مرتبطة بأمر توريد
            document.querySelectorAll('.attach-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deliveryId = this.getAttribute('data-delivery-id');
                    const supplierId = this.getAttribute('data-supplier-id');
                    document.getElementById('attachDeliveryId').value = deliveryId;
                    document.getElementById('attachForm').action = '/fuel-delivery/' + deliveryId + '/attach-order';
                    loadAttachOrders(supplierId);
                    document.getElementById('attachModal').classList.remove('hidden');
                });
            });
        });
    </script>
@endsection
