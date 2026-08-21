@extends('layouts.app')

@section('title', 'المستودعات')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
@endsection

@section('content')
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">المستودعات</h2>
            @can('warehouses.create')
                <a href="{{ route('warehouses.create') }}"
                    class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    + اضافة مستودع جديد
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead class="bg-primary-strong text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">اسم المستودع</th>
                        <th class="px-4 py-3 text-right">جازولين</th>
                        <th class="px-4 py-3 text-right">بنزين</th>
                        <th class="px-4 py-3 text-right">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $warehouse->name }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $gasoline = $warehouse->stocks->firstWhere('fuel_type', 1);
                                @endphp
                                {{ formatNumber($gasoline->current_stock ?? 0) }} لتر
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $benzine = $warehouse->stocks->firstWhere('fuel_type', 2);
                                @endphp
                                {{ formatNumber($benzine->current_stock ?? 0) }} لتر
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                @can('warehouses.edit')
                                    <a href="{{ route('warehouses.edit', $warehouse) }}"
                                        class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700">تعديل</a>
                                @endcan
                                @can('warehouses.delete')
                                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST">
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
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">لا توجد مستودعات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    let form = this.closest('form');
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
                        if (result.isConfirmed) { form.submit(); }
                    });
                });
            });
        });
    </script>
@endsection
