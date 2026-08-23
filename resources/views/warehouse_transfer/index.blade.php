@extends('layouts.app')

@section('title', 'تحويلات المستودع')

@section('styles')
    <link href="{{ asset('vendor/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 5px 10px; font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">تحويلات المستودع</h2>
            @can('warehouse_transfers.create')
                <a href="{{ route('warehouse_transfers.create') }}"
                    class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    + اضافة تحويل جديد
                </a>
            @endcan
        </div>

        <form action="{{ route('warehouse_transfers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">المستودع</label>
                <select name="warehouse_id" class="w-full select2">
                    <option value="">الكل</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">نوع الوقود</label>
                <select name="fuel_type" class="w-full p-2 border rounded-lg">
                    <option value="">الكل</option>
                    <option value="1" {{ request('fuel_type') == 1 ? 'selected' : '' }}>جازولين</option>
                    <option value="2" {{ request('fuel_type') == 2 ? 'selected' : '' }}>بنزين</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full p-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">الى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full p-2 border rounded-lg">
            </div>
            <div class="md:col-span-4 flex justify-center">
                <button type="submit" class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition">بحث</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead class="bg-primary-strong text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">من مستودع</th>
                        <th class="px-4 py-3 text-right">الى مستودع</th>
                        <th class="px-4 py-3 text-right">نوع الوقود</th>
                        <th class="px-4 py-3 text-right">الكمية</th>
                        <th class="px-4 py-3 text-right">ملاحظة</th>
                        <th class="px-4 py-3 text-right">الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $transfers->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">{{ $transfer->date->format('Y/m/d') }}</td>
                            <td class="px-4 py-3">{{ $transfer->warehouse->name }}</td>
                            <td class="px-4 py-3">{{ $transfer->relatedTransaction->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $transfer->fuel_type == 1 ? 'جازولين' : 'بنزين' }}</td>
                            <td class="px-4 py-3">{{ formatNumber($transfer->quantity) }}</td>
                            <td class="px-4 py-3">{{ $transfer->note ?? '-' }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                @can('warehouse_transfers.delete')
                                    <form action="{{ route('warehouse_transfers.destroy', $transfer) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">حذف</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">لا توجد تحويلات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $transfers->links() }}</div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ placeholder: "اختر المستودع", allowClear: true, width: '100%' });
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    let form = this.closest('form');
                    Swal.fire({
                        title: 'هل أنت متأكد؟', text: "لن تتمكن من التراجع!", icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذفها', cancelButtonText: 'إلغاء'
                    }).then((result) => { if (result.isConfirmed) { form.submit(); } });
                });
            });
        });
    </script>
@endsection
