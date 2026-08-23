@extends('layouts.app')

@section('title', $station->name . ' — لوحة المحطة')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <style>
        .hub-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.9rem;
            border-radius: 0.6rem;
            color: var(--color-text);
            font-weight: 500;
            transition: background-color .15s ease, color .15s ease;
        }
        .hub-link:hover { background-color: var(--color-surface-2); }
        .hub-link.active {
            background-color: var(--color-primary-strong);
            color: #fff;
            font-weight: 700;
        }
        .hub-sublink {
            display: block;
            padding: 0.4rem 0.9rem;
            margin-inline-start: 1rem;
            border-inline-start: 2px solid var(--color-border);
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
        .hub-sublink:hover { color: var(--color-primary); border-inline-start-color: var(--color-primary); }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- الرأس -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('station.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← الرئيسية</a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $station->name }}</h1>
        </div>

        <!-- إجراءات سريعة -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                إجراءات سريعة
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-cloak x-show="open" @click.away="open = false"
                class="absolute left-0 mt-2 w-48 bg-white border rounded-lg shadow-lg py-2 z-50">
                @can('stations.edit')
                    <button type="button" onclick="openStationEditModal()"
                        class="block w-full text-right px-4 py-2 hover:bg-gray-100 text-gray-700">تعديل بيانات المحطة</button>
                @endcan
                @can('stations.delete')
                    <form method="POST" action="{{ route('station.destroy', $station->id) }}"
                        onsubmit="return confirmDelete(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full text-right px-4 py-2 hover:bg-gray-100 text-red-600">حذف المحطة</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- الشريط الجانبي (عمودي على الشاشات الكبيرة) -->
        <aside class="lg:w-56 shrink-0">
            <nav class="hidden lg:block bg-white rounded-2xl shadow-lg p-3 space-y-1 sticky top-6">
                <a href="{{ route('stations.hub', $station->id) }}" class="hub-link active">
                    نظرة عامة
                </a>

                <p class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400">إعدادات المحطة</p>
                @canany(['stocks.create', 'machines.create'])
                    <a href="{{ route('station_setup.index', $station->id) }}" class="hub-sublink">تهيئة المحطة</a>
                @endcanany
                @can('stocks.view')
                    <a href="{{ route('stock.index', $station->id) }}" class="hub-sublink">قائمة الآبار</a>
                @endcan
                @can('employees.view')
                    <a href="{{ route('employee.index', $station->id) }}" class="hub-sublink">قائمة الموظفين</a>
                @endcan

                <p class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400">العمليات</p>
                @can('machine_details.create')
                    <a href="{{ route('machine_detail.create', $station->id) }}" class="hub-sublink">تسجيل العدادات</a>
                @endcan
                @can('machine_details.view')
                    <a href="{{ route('machine_detail.index', $station->id) }}" class="hub-sublink">قائمة العدادات</a>
                @endcan
                @can('tunckers.create')
                    <a href="{{ route('tuncker.create', $station->id) }}" class="hub-sublink">تسجيل تنكر</a>
                @endcan
                @can('tunckers.view')
                    <a href="{{ route('tuncker.index', $station->id) }}" class="hub-sublink">قائمة التناكر</a>
                @endcan

                <p class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400">الودائع</p>
                @can('deposit_details.create')
                    <a href="{{ route('deposit_detail.create', $station->id) }}" class="hub-sublink">تسجيل التوريدات</a>
                @endcan
                @can('deposit_details.view')
                    <a href="{{ route('deposit_detail.index', $station->id) }}" class="hub-sublink">قائمة التوريدات</a>
                @endcan

                <p class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400">المصروفات</p>
                @can('expenses.create')
                    <a href="{{ route('expense.create', $station->id) }}" class="hub-sublink">تسجيل المصروفات</a>
                @endcan
                @can('expenses.view')
                    <a href="{{ route('expense.index', $station->id) }}" class="hub-sublink">قائمة المصروفات</a>
                @endcan

                <p class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400">الحسابات</p>
             {{--    @can('clients.view')
                    <a href="{{ route('client.station', $station->id) }}" class="hub-sublink">إدارة الحسابات لدى المحطة</a>
                @endcan --}}
            </nav>

            <!-- شريط تبويبات أفقي للموبايل -->
            <nav class="lg:hidden flex overflow-x-auto gap-2 bg-white rounded-xl shadow p-2 -mx-1 px-1"
                style="scrollbar-width: thin;">
                <a href="{{ route('stations.hub', $station->id) }}"
                    class="shrink-0 px-4 py-2 rounded-lg text-sm font-semibold bg-primary-strong text-white">نظرة عامة</a>
                @can('machine_details.create')
                    <a href="{{ route('machine_detail.create', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">تسجيل العدادات</a>
                @endcan
                @can('machine_details.view')
                    <a href="{{ route('machine_detail.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">قائمة العدادات</a>
                @endcan
                @can('tunckers.create')
                    <a href="{{ route('tuncker.create', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">تسجيل تنكر</a>
                @endcan
                @can('tunckers.view')
                    <a href="{{ route('tuncker.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">التناكر</a>
                @endcan
                @can('deposit_details.create')
                    <a href="{{ route('deposit_detail.create', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">توريدات</a>
                @endcan
                @can('deposit_details.view')
                    <a href="{{ route('deposit_detail.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">قائمة التوريدات</a>
                @endcan
                @can('expenses.create')
                    <a href="{{ route('expense.create', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">مصروفات</a>
                @endcan
                @can('expenses.view')
                    <a href="{{ route('expense.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">قائمة المصروفات</a>
                @endcan
                {{-- @can('clients.view')
                    <a href="{{ route('client.station', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">الحسابات</a>
                @endcan --}}
                @can('stock.index')
                    <a href="{{ route('stock.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">الآبار</a>
                @endcan
                @canany(['stocks.create', 'machines.create'])
                    <a href="{{ route('station_setup.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">تهيئة المحطة</a>
                @endcanany
                @can('employees.view')
                    <a href="{{ route('employee.index', $station->id) }}" class="shrink-0 px-4 py-2 rounded-lg text-sm bg-gray-100 text-gray-600 whitespace-nowrap">الموظفون</a>
                @endcan
            </nav>
        </aside>

        <!-- المحتوى -->
        <main class="flex-1 min-w-0">
            <!-- إحصائيات سريعة لليوم -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <p class="text-sm text-gray-500 mb-1">توريدات اليوم</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($stats['deposits']) }} <span class="text-xs text-gray-400">ج.س</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <p class="text-sm text-gray-500 mb-1">مصروفات اليوم</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['expenses']) }} <span class="text-xs text-gray-400">ج.س</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <p class="text-sm text-gray-500 mb-1">صافي قراءات اليوم</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($stats['readings']) }} <span class="text-xs text-gray-400">لتر</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-5 {{ $stats['pending_approvals'] > 0 ? 'ring-2 ring-amber-300' : '' }}">
                    <p class="text-sm text-gray-500 mb-1">قراءات بانتظار الاعتماد</p>
                    <p class="text-2xl font-bold {{ $stats['pending_approvals'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $stats['pending_approvals'] }}</p>
                    @if ($stats['pending_approvals'] > 0)
                        <a href="{{ route('machine_details.pending') }}" class="text-xs text-primary hover:underline">مراجعة الآن ←</a>
                    @endif
                </div>
            </div>

            <!-- نظرة عامة -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">نظرة عامة</h2>
                <p class="text-gray-500 text-sm">اختر قسمًا من القائمة الجانبية للانتقال إلى صفحات المحطة.</p>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        function confirmDelete(e) {
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                title: 'حذف المحطة؟',
                text: 'لا يمكن التراجع عن هذه العملية!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذفها',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }

        function openStationEditModal() {
            Swal.fire({
                title: 'تعديل بيانات المحطة',
                input: 'text',
                inputLabel: 'اسم المحطة',
                inputValue: '{{ $station->name }}',
                showCancelButton: true,
                confirmButtonText: 'حفظ',
                cancelButtonText: 'إلغاء',
                inputValidator: (value) => {
                    if (!value || !value.trim()) return 'يجب إدخال اسم المحطة';
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.trim()) {
                    const f = document.createElement('form');
                    f.method = 'POST';
                    f.action = "{{ route('station.update', $station->id) }}";
                    f.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="name" value="${result.value.trim().replace(/"/g, '&quot;')}">
                    `;
                    document.body.appendChild(f);
                    f.submit();
                }
            });
        }
    </script>
@endsection
