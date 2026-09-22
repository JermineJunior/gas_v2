@extends('layouts.app')

@section('title', 'قائمة المتابعة — التوريدات غير المرتبطة بطلبية')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('content')
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">التوريدات غير المرتبطة بطلبية</h2>
                <p class="text-sm text-gray-500 mt-1">التسليمات المسجلة بدون أمر توريد — يُحسب نوع الوقود والتكلفة بعد الربط من هنا</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 bg-amber-50 border border-amber-300 text-amber-700 text-sm font-semibold rounded-full px-4 py-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    {{ $deliveries->total() }} توريدة غير مرتبطة
                </span>
                <a href="{{ url('/') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">رجوع</a>
            </div>
        </div>

        <!-- الفلاتر -->
        <form method="GET" action="{{ route('tuncker.unmatched') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-primary-soft rounded-lg p-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المحطة</label>
                <select name="station_id" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="">كل المحطات</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(request('station_id') == $station->id)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-primary-strong text-white px-4 py-2 rounded-lg hover:bg-primary-strong transition">بحث</button>
                <a href="{{ route('tuncker.unmatched') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">مسح</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">المحطة</th>
                        <th class="px-4 py-3 text-right">اسم المورد</th>
                        <th class="px-4 py-3 text-right">نوع الوقود</th>
                        <th class="px-4 py-3 text-right">الكمية (لتر)</th>
                        <th class="px-4 py-3 text-right">رقم التنكر</th>
                        <th class="px-4 py-3 text-right">السائق</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        @canany(['tunckers.view', 'tunckers.edit'])
                            <th class="px-4 py-3 text-right">الاجراءات</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deliveries as $delivery)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $deliveries->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">{{ $delivery->date->format('Y/m/d') }}</td>
                            <td class="px-4 py-3">{{ $delivery->station->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $delivery->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $delivery->tuncker?->fuel_type == 1 ? 'جازولين' : ($delivery->tuncker?->fuel_type == 2 ? 'بنزين' : '-') }}</td>
                            <td class="px-4 py-3">{{ number_format($delivery->quantity) }}</td>
                            <td class="px-4 py-3">{{ $delivery->tuncker?->tuncker_no ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $delivery->tuncker?->driver_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block bg-amber-100 text-amber-700 border border-amber-300 rounded-full px-2.5 py-0.5 text-xs font-semibold">بدون طلبية</span>
                            </td>
                            @canany(['tunckers.view', 'tunckers.edit'])
                                <td class="px-4 py-3">
                                    @can('tunckers.view')
                                        <a href="{{ $delivery->tuncker ? route('tuncker.show', $delivery->tuncker->id) : '#' }}"
                                            class="bg-sky-600 text-white px-3 py-1 rounded-lg hover:bg-sky-700">عرض</a>
                                    @endcan
                                    @can('tunckers.edit')
                                        <button type="button"
                                            class="attach-btn bg-amber-500 text-white px-3 py-1 rounded-lg hover:bg-amber-600"
                                            data-delivery-id="{{ $delivery->id }}"
                                            data-supplier-id="{{ $delivery->supplier_id }}">
                                            ربط طلبية
                                        </button>
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-500 bg-green-50">
                                لا توجد توريدات غير مرتبطة بطلبية في هذه الفلاتر.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $deliveries->appends($appends)->links() }}</div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ URL::asset('form_validation/jquery.form.js') }}"></script>
    @include('messages')
    <script>
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