@extends('layouts.app')

@section('title', 'إدارة العدادات')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('content')
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">قائمة العدادات</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">الموظف</th>
                        <th class="px-4 py-3 text-right">سعر اللتر</th>
                        <th class="px-4 py-3 text-right">صافي اللتر</th>
                        <th class="px-4 py-3 text-right">الإجمالي</th>
                        <th class="px-4 py-3 text-right">إجمالي المبيعات (نهاية الوردية)</th>
                        <th class="px-4 py-3 text-right">المصروفات</th>
                        <th class="px-4 py-3 text-right">الصافي المستحق على الموظف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($machines as $date => $dayMachines)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $date }}</td>
                            <td class="px-4 py-3">{{ $dayMachines->first()->employee->name }}</td>
                            <td class="px-4 py-3">{{ number_format($dayMachines->first()->price) }}</td>
                            <td class="px-4 py-3">{{ number_format($dayMachines->sum('net')) }}</td>
                            <td class="px-4 py-3">{{ number_format($dayMachines->sum('total')) }}</td>
                            <td class="px-4 py-3">{{ number_format($summaries[$date]['shift_total'] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ number_format($summaries[$date]['expenses_total'] ?? 0) }}</td>
                            <td class="px-4 py-3 font-bold text-primary-strong">{{ number_format($summaries[$date]['net_owed'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-4">
                                لا توجد أي عمليات متاحة حاليًا.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
@endsection
