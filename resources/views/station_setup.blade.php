@extends('layouts.app')

@section('title', 'تهيئة المحطة — ' . $station->name)

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .select2-container .select2-selection--single {
            height: 42px !important;
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding-left: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 8px;
        }
    </style>
@endsection

@section('content')
    <div x-data="{
        openStock: false,
        openMachine: false,
        openGun: false,
        machineForm: { stock_id: '', stock_name: '' },
        gunForm: { machine_id: '', machine_name: '' },
        editStock: { id: '', name: '', type: '1', qty: 0 },
        openEditStock: false,
        editMachine: { id: '', name: '', max_counter: 9999999, use_rollover: true },
        openEditMachine: false,
        editGun: { id: '', name: '' },
        openEditGun: false
    }">
        <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">

            <!-- رأس الصفحة -->
            <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
                <h2 class="text-2xl font-bold text-gray-800">تهيئة المحطة — {{ $station->name }}</h2>
                @can('stocks.create')
                    <button @click="openStock = true"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        + إضافة بئر
                    </button>
                @endcan
            </div>

            <!-- إعدادات العدادات -->
        <div class="max-w-7xl mx-auto mb-6 bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">إعدادات العدادات</h3>
            <form action="{{ route('station_setup.meter_settings', $station->id) }}" method="POST" id="meterSettingsForm"
                class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">الحد الافتراضي للعداد</label>
                    <input type="text" name="default_max_counter" id="defaultMaxCounter"
                        value="{{ $station->default_max_counter ? number_format($station->default_max_counter) : '' }}"
                        placeholder="100,000"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="use_rollover" value="1" checked
                            class="w-4 h-4 accent-green-600">
                        تفعيل حساب تصفير العداد افتراضيًا
                    </label>
                </div>
                <div></div>
                <div>
                    <button type="submit"
                        class="w-full bg-accent-strong text-white px-4 py-2 rounded-lg hover:bg-accent-strong transition font-semibold">
                        تطبيق على كل ماكينات المحطة
                    </button>
                </div>
            </form>
            <p class="text-xs text-gray-400 mt-2">التطبيق الجماعي يستبدل إعدادات الحد الأقصى والتصفير لكل ماكينات المحطة الحالية.</p>
        </div>

        <!-- بطاقات الابار -->
            <div class="space-y-6">
                @forelse ($stocks as $stock)
                    @php
                        $stockMachines = $stock->machines->sortBy('id');
                    @endphp
                    <div class="border border-gray-200 rounded-2xl overflow-hidden">
                        <!-- رأس البير -->
                        <div class="bg-gray-50 border-b px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold text-gray-800">{{ $stock->name }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $stock->type == 1 ? 'bg-green-100 text-green-700' : 'bg-sky-100 text-sky-700' }}">
                                    {{ $stock->type == 1 ? 'جازولين' : 'بنزين' }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-accent-soft text-accent-strong">
                                    الرصيد: {{ formatNumber($stock->qty) }} لتر
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                @can('machines.create')
                                    <button
                                        @click="machineForm = { stock_id: {{ $stock->id }}, stock_name: '{{ $stock->name }}' }; openMachine = true;"
                                        class="bg-primary-strong text-white px-4 py-1.5 rounded-lg text-sm shadow hover:bg-primary-strong transition">
                                        + إضافة ماكينة
                                    </button>
                                @endcan
                                @can('stocks.edit')
                                    <button type="button"
                                        @click="
                                            editStock = { id: {{ $stock->id }}, name: '{{ $stock->name }}', type: '{{ $stock->type }}', qty: {{ $stock->qty }} };
                                            openEditStock = true;
                                            setTimeout(() => { $('.type-edit').val(editStock.type).trigger('change'); }, 100);
                                        "
                                        class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700 transition"
                                        title="تعديل البير">✎</button>
                                @endcan
                                @can('stocks.delete')
                                    <form action="{{ route('station_setup.stock.delete', $stock->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-red-700 transition"
                                            title="حذف البير">✕</button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                        <!-- صف الماكينات -->
                        <div class="p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:overflow-x-auto sm:pb-2">
                                @foreach ($stockMachines as $machine)
                                    @php
                                        $guns = $machine->guns->sortBy('id')->values();
                                        $gun1 = $guns->get(0);
                                        $gun2 = $guns->get(1);
                                        $hasTwo = $guns->count() >= 2;
                                    @endphp
                                    <div class="min-w-full sm:min-w-[260px] bg-gray-50 border border-gray-200 rounded-xl p-3 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                        <!-- المسدس الأول (يمين في RTL / أعلى في الموبايل) -->
                                        <div class="w-full sm:w-24">
                                            @if ($gun1)
                                                <div class="relative bg-white border border-gray-300 rounded-lg px-2 py-2 text-center">
                                                    @can('machines.edit')
                                                        <button type="button"
                                                            @click="editGun = { id: {{ $gun1->id }}, name: '{{ $gun1->name }}' }; openEditGun = true;"
                                                            class="absolute top-1 left-1 text-green-600 hover:text-green-700 text-lg leading-none px-0.5"
                                                            title="تعديل / حذف المسدس">✎</button>
                                                    @endcan
                                                    <p class="text-[10px] text-gray-400">مسدس</p>
                                                    <p class="text-sm font-bold text-gray-800">{{ $gun1->name }}</p>
                                                </div>
                                            @else
                                                @can('machines.create')
                                                    <button
                                                        @click="gunForm = { machine_id: {{ $machine->id }}, machine_name: '{{ $machine->name }}' }; openGun = true;"
                                                        class="w-full border-2 border-dashed border-gray-300 rounded-lg px-2 py-3 text-xs text-gray-500 hover:border-primary hover:text-primary transition">
                                                        + مسدس
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>

                                        <!-- الماكينة -->
                                        <div class="relative bg-primary-softer border border-primary-soft rounded-lg px-4 py-4 text-center w-full sm:w-auto">
                                            @can('machines.edit')
                                                <button type="button"
                                                    @click="editMachine = { id: {{ $machine->id }}, name: '{{ $machine->name }}', max_counter: {{ $machine->max_counter ?: 9999999 }}, use_rollover: {{ $machine->use_rollover ? 'true' : 'false' }} }; openEditMachine = true;"
                                                    class="absolute top-1 left-1 text-green-600 hover:text-green-700 text-lg leading-none px-0.5"
                                                    title="تعديل / حذف الماكينة">✎</button>
                                            @endcan
                                            <p class="text-[10px] text-gray-400">ماكينة</p>
                                            <p class="font-bold text-gray-800">{{ $machine->name }}</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5">
                                                الحد الأقصى: {{ number_format($machine->max_counter ?: 9999999) }}
                                                {{ $machine->use_rollover ? '' : '· بدون تصفير' }}
                                            </p>
                                        </div>

                                        <!-- المسدس الثاني (يسار في RTL / أسفل في الموبايل) -->
                                        <div class="w-full sm:w-24">
                                            @if ($gun2)
                                                <div class="relative bg-white border border-gray-300 rounded-lg px-2 py-2 text-center">
                                                    @can('machines.edit')
                                                        <button type="button"
                                                            @click="editGun = { id: {{ $gun2->id }}, name: '{{ $gun2->name }}' }; openEditGun = true;"
                                                            class="absolute top-1 left-1 text-green-600 hover:text-green-700 text-lg leading-none px-0.5"
                                                            title="تعديل / حذف المسدس">✎</button>
                                                    @endcan
                                                    <p class="text-[10px] text-gray-400">مسدس</p>
                                                    <p class="text-sm font-bold text-gray-800">{{ $gun2->name }}</p>
                                                </div>
                                            @elseif (!$hasTwo)
                                                @can('machines.create')
                                                    <button
                                                        @click="gunForm = { machine_id: {{ $machine->id }}, machine_name: '{{ $machine->name }}' }; openGun = true;"
                                                        class="w-full border-2 border-dashed border-gray-300 rounded-lg px-2 py-3 text-xs text-gray-500 hover:border-primary hover:text-primary transition">
                                                        + مسدس
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <!-- بطاقة إضافة ماكينة (متقطعة) -->
                                @can('machines.create')
                                    <button
                                        @click="machineForm = { stock_id: {{ $stock->id }}, stock_name: '{{ $stock->name }}' }; openMachine = true;"
                                        class="min-w-full sm:min-w-[160px] min-h-[120px] border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center text-gray-400 hover:border-primary hover:text-primary transition">
                                        <span class="text-sm font-semibold">+ إضافة ماكينة</span>
                                    </button>
                                @endcan
                            </div>

                            @if ($stockMachines->isEmpty())
                                <p class="text-center text-sm text-gray-400 mt-2">لا توجد ماكينات لهذا البير بعد</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-lg p-4">
                        لا توجد ابار مسجلة لهذه المحطة بعد.
                    </div>
                @endforelse

                <!-- بطاقة إضافة بئر (متقطعة) -->
                @can('stocks.create')
                    <button @click="openStock = true"
                        class="w-full min-h-[90px] border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center text-gray-400 hover:border-primary hover:text-primary transition">
                        <span class="font-semibold">+ إضافة بئر جديد</span>
                    </button>
                @endcan
            </div>
        </div>

        <!-- 🟦 مودال إضافة بئر -->
        <div id="addStockModal" x-cloak x-show="openStock" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openStock = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openStock = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">إضافة بئر جديد</h2>

                <form action="{{ route('station_setup.stock.store', $station->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                    <div>
                        <label class="block text-gray-700 mb-1">الاسم</label>
                        <input type="text" name="name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">نوع البير</label>
                        <select name="type" required
                            class="type w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                            <option value="1">جازولين</option>
                            <option value="2">بنزين</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">الرصيد الابتدائي (لتر)</label>
                        <input type="number" name="qty" min="0" step="any" value="0"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
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

        <!-- 🟩 مودال إضافة ماكينة -->
        <div x-cloak x-show="openMachine" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openMachine = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openMachine = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">إضافة ماكينة</h2>
                <p class="text-sm text-gray-500 text-center mb-4">البير: <b x-text="machineForm.stock_name"></b></p>

                <form action="{{ route('station_setup.machine.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                    <input type="hidden" name="stock_id" :value="machineForm.stock_id">
                    <div>
                        <label class="block text-gray-700 mb-1">اسم/رقم الماكينة</label>
                        <input type="text" name="name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">الحد الأقصى للعداد</label>
                        <input type="number" name="max_counter" min="1" step="any" placeholder="9999999"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1">اتركه فارغاً لاستخدام القيمة الافتراضية 9,999,999</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="use_rollover" value="1" checked
                                class="w-4 h-4 accent-green-600">
                            تفعيل حساب تصفير العداد لهذه الماكينة
                        </label>
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

        <!-- 🟨 مودال إضافة مسدس -->
        <div x-cloak x-show="openGun" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openGun = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openGun = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">إضافة مسدس</h2>
                <p class="text-sm text-gray-500 text-center mb-4">الماكينة: <b x-text="gunForm.machine_name"></b></p>

                <form action="{{ route('station_setup.gun.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                    <input type="hidden" name="machine_id" :value="gunForm.machine_id">
                    <div>
                        <label class="block text-gray-700 mb-1">اسم/رقم المسدس</label>
                        <input type="text" name="name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
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

        <!-- 🟩 مودال تعديل البير -->
        <div x-cloak x-show="openEditStock" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openEditStock = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openEditStock = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">تعديل البير</h2>

                <form action="{{ route('station_setup.stock.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editStock.id">
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                    <div>
                        <label class="block text-gray-700 mb-1">الاسم</label>
                        <input type="text" name="name" :value="editStock.name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">نوع البير</label>
                        <select name="type" required
                            class="type-edit w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                            <option value="1">جازولين</option>
                            <option value="2">بنزين</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">الرصيد (لتر)</label>
                        <input type="number" name="qty" min="0" step="any" :value="editStock.qty"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
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

        <!-- 🟨 مودال تعديل الماكينة -->
        <div x-cloak x-show="openEditMachine" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openEditMachine = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openEditMachine = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">تعديل الماكينة</h2>

                <form action="{{ route('station_setup.machine.update') }}" method="POST" id="editMachineForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editMachine.id">
                    <div>
                        <label class="block text-gray-700 mb-1">اسم/رقم الماكينة</label>
                        <input type="text" name="name" :value="editMachine.name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 mb-1">الحد الأقصى للعداد</label>
                        <input type="number" name="max_counter" min="1" step="any" :value="editMachine.max_counter"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1">القيمة الافتراضية 9,999,999</p>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="use_rollover" value="1" :checked="editMachine.use_rollover"
                                class="w-4 h-4 accent-green-600">
                            تفعيل حساب تصفير العداد لهذه الماكينة
                        </label>
                    </div>
                </form>
                <div class="flex justify-between items-center mt-2">
                    @can('machines.delete')
                        <form :action="'{{ url('station-setup/machine') }}/' + editMachine.id" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                class="delete-btn bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                                حذف
                            </button>
                        </form>
                    @endcan
                    <button type="submit" form="editMachineForm"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        تحديث
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟧 مودال تعديل المسدس -->
        <div x-cloak x-show="openEditGun" x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
            <div @click.away="openEditGun = false" class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
                <button @click="openEditGun = false" class="absolute top-3 left-3 text-gray-500 hover:text-gray-700">✖</button>
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">تعديل المسدس</h2>

                <form action="{{ route('station_setup.gun.update') }}" method="POST" id="editGunForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editGun.id">
                    <div>
                        <label class="block text-gray-700 mb-1">اسم/رقم المسدس</label>
                        <input type="text" name="name" :value="editGun.name" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                </form>
                <div class="flex justify-between items-center mt-2">
                    @can('machines.delete')
                        <form :action="'{{ url('station-setup/gun') }}/' + editGun.id" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                class="delete-btn bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                                حذف
                            </button>
                        </form>
                    @endcan
                    <button type="submit" form="editGunForm"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        تحديث
                    </button>
                </div>
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
            // 🔹 تنسيق الحد الافتراضي للعداد بفواصل
            function formatNumberInput(value) {
                let cleaned = String(value || '').replace(/,/g, '');
                if (cleaned === '') return '';
                let n = parseFloat(cleaned);
                if (isNaN(n)) return '';
                return n.toLocaleString('en-US', { maximumFractionDigits: 2 });
            }

            $('#defaultMaxCounter').on('input', function() {
                this.value = formatNumberInput(this.value);
            });

            // 🔹 تأكيد قبل التطبيق الجماعي لإعدادات العدادات
            $('#meterSettingsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'تطبيق على كل ماكينات المحطة؟',
                    text: 'سيتم استبدال إعدادات الحد الأقصى والتصفير لجميع الماكينات الحالية',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، تطبيق',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const input = form.querySelector('#defaultMaxCounter');
                        if (input) input.value = String(input.value).replace(/,/g, '');
                        form.submit();
                    }
                });
            });

            $('.type').select2({
                width: '100%',
                placeholder: 'اختر نوع الوقود الموجود في البير',
                dropdownParent: $('#addStockModal')
            });

            $('.type-edit').select2({
                width: '100%',
                dropdownParent: $('[x-show="openEditStock"]')
            });

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
                        if (result.isConfirmed) {
                            form.submit();
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
                window.addEventListener('click', function(e) {
                    if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endsection
