@extends('layouts.app')

@section('title', 'العملاء')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@php
    $tabTotals = [
        'all' => ['total' => $clients->sum('total_sum'), 'paid' => $clients->sum('paid_sum')],
        '1'   => ['total' => $clients->where('type', 1)->sum('total_sum'), 'paid' => $clients->where('type', 1)->sum('paid_sum')],
        '2'   => ['total' => $clients->where('type', 2)->sum('total_sum'), 'paid' => $clients->where('type', 2)->sum('paid_sum')],
    ];
@endphp

@section('content')
    <!-- قسم الحسابات -->
    <div x-data="{ showAddModal: false, showEditModal: false, editclient: { id: '', name: '', phone: '', type: '' }, activeTab: (function(){ try { return localStorage.getItem('clientActiveTab') || 'all' } catch(e) { return 'all' } })(), totals: @js($tabTotals), fmt(n) { return Number(n || 0).toLocaleString(); } }" x-init="$watch('activeTab', v => { window._clientActiveTab = v; try { localStorage.setItem('clientActiveTab', v) } catch(e) {} }); window._clientActiveTab = activeTab" class="max-w-5xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة العملاء</h2>

            <!-- زر إضافة -->
            @if (!isset($station))
                @can('clients.create')
                <div class="flex gap-1">
                    <button @click="showAddModal = true"
                        class="bg-primary-strong text-white px-5 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                        + إضافة عميل جديد
                    </button>
                </div>
                @endcan
            @endif
        </div>

        <!-- 🔍 مربع البحث -->
        @if (!isset($station))
            <div class="mb-6">
                <input type="text" id="searchInput" placeholder="ابحث عن العميل..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring focus:ring-primary">
            </div>
        @endif

        <!-- تبويبات أنواع العملاء -->
        <div class="flex gap-2 mb-6">
            <button @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-primary-strong text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-5 py-2 rounded-lg font-semibold transition">
                الكل
            </button>
            <button @click="activeTab = '1'"
                :class="activeTab === '1' ? 'bg-primary-strong text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-5 py-2 rounded-lg font-semibold transition">
                العملاء
            </button>
            <button @click="activeTab = '2'"
                :class="activeTab === '2' ? 'bg-primary-strong text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-5 py-2 rounded-lg font-semibold transition">
                الباصات
            </button>
        </div>

        <!-- ملخص المديونيات والإيرادات حسب التبويب -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="bg-primary-soft rounded-xl px-5 py-3 shadow-sm min-w-[180px]">
                <p class="text-sm text-gray-600 mb-1">إجمالي المديونيات</p>
                <p id="totalDebtVal" class="text-xl font-bold" x-text="fmt(totals[activeTab]?.total) + ' ج.س'"></p>
            </div>
            <div class="bg-green-50 rounded-xl px-5 py-3 shadow-sm min-w-[180px]">
                <p class="text-sm text-gray-600 mb-1">إجمالي الإيرادات</p>
                <p id="totalPaidVal" class="text-xl font-bold text-green-700" x-text="fmt(totals[activeTab]?.paid) + ' ج.س'"></p>
            </div>
            <div class="bg-accent-soft rounded-xl px-5 py-3 shadow-sm min-w-[180px]">
                <p class="text-sm text-gray-600 mb-1">الرصيد المتبقي</p>
                <p id="totalBalanceVal" class="text-xl font-bold text-accent-strong" x-text="fmt((totals[activeTab]?.total || 0) - (totals[activeTab]?.paid || 0)) + ' ج.س'"></p>
            </div>
        </div>

        <!-- جدول الحسابات -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-3 py-2.5 text-center text-sm font-semibold">اسم العميل</th>
                        <th class="px-3 py-2.5 text-center text-sm font-semibold">النوع</th>
                        <th class="px-3 py-2.5 text-center text-sm font-semibold">الرصيد الإجمالي</th>
                        <th class="px-3 py-2.5 text-center text-sm font-semibold">آخر توريدة/سداد</th>
                        <th class="px-3 py-2.5 text-center text-sm font-semibold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="clientTableBody">
                    @if ($clients->isEmpty())
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-600">لا يوجد عملاء مسجلون حالياً.</td>
                        </tr>
                    @else
                        @foreach ($clients as $client)
                        
                            @php $clientBalance = $client->total_sum - $client->paid_sum; @endphp
                            <tr data-type="{{ $client->type }}"
                                x-show="activeTab === 'all' || activeTab == '{{ $client->type }}'"
                                class="border-b border-gray-100 hover:bg-gray-50 transition">

                                <!-- اسم العميل -->
                                <td class="px-3 py-3">
                                    <a href="{{ route('client.show', $client->id) }}"
                                        class="font-semibold text-gray-800 hover:text-primary-strong hover:underline transition-colors">
                                        {{ $client->name }}
                                    </a>
                                </td>

                                <!-- النوع -->
                                <td class="px-3 py-3 text-center">
                                    <span
                                        class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $client->type == 2 ? 'bg-purple-100 text-purple-700' : 'bg-primary-soft text-primary-strong' }}">
                                        {{ $client->type == 2 ? 'باص' : 'عميل' }}
                                    </span>
                                </td>

                                <!-- الرصيد الإجمالي -->
                                <td class="px-3 py-3 text-center font-bold {{ $clientBalance >= 0 ? 'text-primary-strong' : 'text-red-600' }}">
                                    {{ formatNumber($clientBalance) }} ج.س
                                </td>

                                <!-- آخر توريدة/سداد -->
                                <td class="px-3 py-3 text-center text-sm {{ $client->last_payment_date ? 'text-gray-500' : 'text-red-600 font-semibold' }}">
                                    @if ($client->last_payment_date)
                                     آخر توريدة/سداد: {{ $client->last_payment_date ? \Carbon\Carbon::parse($client->last_payment_date, config('app.timezone'))->diffForHumans() : 'لا يوجد' }}

                                    @else
                                        لم يقم بأي توريدة/سداد
                                    @endif
                                </td>

                                <!-- الإجراءات -->
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <a href="{{ route('client.show', $client->id) }}"
                                            class="text-xs text-center bg-white border border-primary-strong text-primary-strong px-3 py-1.5 rounded-lg hover:bg-primary-soft transition font-semibold inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                            </svg>
                                            المديونيات
                                        </a>

                                        <a href="{{ route('revenue.index', $client->id) }}"
                                            class="text-xs text-center bg-white border border-green-600 text-green-600 px-3 py-1.5 rounded-lg hover:bg-green-50 transition font-semibold inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            الإيرادات
                                        </a>

                                        <!-- زر ثلاث نقاط + قائمة منسدلة -->
                                        <div x-data="{ openMenu: false }" class="relative">
                                            <button @click="openMenu = !openMenu"
                                                class="bg-gray-200 hover:bg-gray-300 p-1.5 rounded-full transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600"
                                                    viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="5" cy="12" r="2" />
                                                    <circle cx="12" cy="12" r="2" />
                                                    <circle cx="19" cy="12" r="2" />
                                                </svg>
                                            </button>

                                            <!-- القائمة المنسدلة -->
                                            <div x-show="openMenu" @click.away="openMenu = false"
                                                class="absolute left-0 mt-2 w-40 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden z-50">

                                                <!-- تعديل -->
                                                @can('clients.edit')
                                                <button
                                                    @click="$dispatch('edit-client', {
                                                        id: {{ $client->id }},
                                                        name: '{{ $client->name }}',
                                                        phone: '{{ $client->phone }}',
                                                        type: '{{ $client->type }}',

                                                    })"
                                                    class="w-full text-right px-4 py-2 hover:bg-gray-100 text-green-600">
                                                    تعديل
                                                </button>
                                                @endcan

                                                <!-- حذف -->
                                                @can('clients.delete')
                                                <form action="{{ route('client.delete', $client->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="delete-btn w-full text-right px-4 py-2 hover:bg-gray-100 text-red-600">
                                                        حذف
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- 🟦 مودال إضافة عميل جديد -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            x-transition>
            <div @click.away="showAddModal = false" class="bg-white w-full max-w-md rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">إضافة عميل جديد</h2>

                <form action="{{ route('client.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">اسم العميل</label>
                            <input type="text" name="name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">نوع العميل</label>
                            <select name="type" required
                                class="type w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                                <option value="1">عميل</option>
                                <option value="2">باص</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">رقم الهاتف</label>
                            <input type="text" name="phone"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring"
                                placeholder="مثلاً: 0912345678">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-strong hover:bg-primary-strong text-white">
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🟩 مودال تعديل عميل (مودال واحد فقط) -->
        <div x-data="editclientModal()" x-cloak x-show="open"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div @click.away="open = false" class="bg-white w-96 p-6 rounded-xl shadow-xl">

                <h2 class="text-xl font-bold mb-3">تعديل بيانات العميل</h2>

                <form :action="updateUrl" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2">اسم العميل:</label>
                            <input type="text" name="name" x-model="name"
                                class="w-full border px-3 py-2 rounded-lg mb-4">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">نوع العميل</label>
                            <select name="type" x-model="type"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                                <option :value="1">عميل</option>
                                <option :value="2">باص</option>
                            </select>


                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">رقم الهاتف</label>
                            <input type="text" name="phone" x-model="phone"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2  mt-6">
                        <button type="button" @click="open = false"
                            class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                            إلغاء
                        </button>

                        <button class="px-4 py-2 bg-primary-strong text-white rounded-lg hover:bg-primary-strong">
                            حفظ التعديلات
                        </button>
                    </div>

                </form>
            </div>
        </div>


    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        $(document).ready(function() {
            let timer = null;


            $("#searchInput").on("keyup", function() {
                clearTimeout(timer);

                timer = setTimeout(() => {
                    let keyword = $(this).val();
                    let activeTab = window._clientActiveTab || '1';

                    $.ajax({
                        url: "{{ route('client.search') }}",
                        method: "GET",
                        data: {
                            keyword
                        },

                        success: function(response) {
                            $("#clientTableBody").empty();

                            let filtered = activeTab === 'all' ? response : response.filter(c => String(c.type) === String(activeTab));

                            // تحديث ملخص المديونيات والإيرادات حسب نتائج البحث
                            let sumTotal = filtered.reduce((s, c) => s + (Number(c.total_sum) || 0), 0);
                            let sumPaid = filtered.reduce((s, c) => s + (Number(c.paid_sum) || 0), 0);
                            $("#totalDebtVal").text(sumTotal.toLocaleString() + ' ج.س');
                            $("#totalPaidVal").text(sumPaid.toLocaleString() + ' ج.س');
                            $("#totalBalanceVal").text((sumTotal - sumPaid).toLocaleString() + ' ج.س');

                            if (filtered.length === 0) {
                                $("#clientTableBody").html(
                                    '<tr><td colspan="5" class="px-3 py-6 text-center text-gray-600">لا توجد نتائج مطابقة.</td></tr>'
                                );
                                return;
                            }

                            filtered.forEach(client => {

                                let balanceNum = (Number(client.total_sum) - Number(client
                                    .paid_sum)) || 0;
                                let balance = balanceNum.toLocaleString();

                                let lastPaymentHtml = '';
                                if (client.last_payment_date) {
                                    let days = Math.floor((Date.now() - new Date(client.last_payment_date).getTime()) / 86400000);
                                    let rtf = new Intl.RelativeTimeFormat('ar', {
                                        numeric: 'auto'
                                    });
                                    let rel;
                                    if (days < 30) rel = rtf.format(-days, 'day');
                                    else if (days < 365) rel = rtf.format(-Math.floor(days / 30), 'month');
                                    else rel = rtf.format(-Math.floor(days / 365), 'year');
                                    lastPaymentHtml =
                                        `<span class="text-gray-500">آخر توريدة/سداد: ${rel}</span>`;
                                } else {
                                    lastPaymentHtml =
                                        `<span class="text-red-600 font-semibold">لم يقم بأي توريدة/سداد</span>`;
                                }

                                let typeLabel = String(client.type) === '2' ? 'باص' : 'عميل';
                                let typeBadgeClass = String(client.type) === '2' ? 'bg-purple-100 text-purple-700' : 'bg-primary-soft text-primary-strong';

                                let revenueBtn = `
                                        <a href="/revenue/${client.id}"
                                            class="text-xs text-center bg-white border border-green-600 text-green-600 px-3 py-1.5 rounded-lg hover:bg-green-50 transition font-semibold inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            الإيرادات
                                        </a>
                                    `;

                                let deleteBtn = '';
                                @can('clients.delete')
                                deleteBtn = `
                                        <form action="/client/delete/${client.id}" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete-btn w-full text-right px-4 py-2 hover:bg-gray-100 text-red-600">
                                                حذف
                                            </button>
                                        </form>
                                    `;
                                @endcan

                                let editBtn = '';
                                @can('clients.edit')
                                editBtn = `
                                        <button
                                            @click="$dispatch('edit-client', { id: ${client.id}, name: '${client.name}', phone: '${client.phone}', type: '${client.type}' })"
                                            class="w-full text-right px-4 py-2 hover:bg-gray-100 text-green-600">
                                            تعديل
                                        </button>
                                    `;
                                @endcan

                                $("#clientTableBody").append(`
                        <tr data-type="${client.type}" x-show="activeTab === 'all' || activeTab == '${client.type}'" class="border-b border-gray-100 hover:bg-gray-50 transition">

                            <td class="px-3 py-3">
                                <a href="/client/${client.id}" class="font-semibold text-gray-800 hover:text-primary-strong hover:underline transition-colors">
                                    ${client.name}
                                </a>
                            </td>

                            <td class="px-3 py-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold ${typeBadgeClass}">
                                    ${typeLabel}
                                </span>
                            </td>

                            <td class="px-3 py-3 text-center font-bold ${balanceNum >= 0 ? 'text-primary-strong' : 'text-red-600'}">
                                ${balance} ج.س
                            </td>

                            <td class="px-3 py-3 text-center text-sm">
                                ${lastPaymentHtml}
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <a href="/client/${client.id}"
                                        class="text-xs text-center bg-white border border-primary-strong text-primary-strong px-3 py-1.5 rounded-lg hover:bg-primary-soft transition font-semibold inline-flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                        المديونيات
                                    </a>
                                    ${revenueBtn}

                                    <div x-data="{ openMenu: false }" class="relative">
                                        <button @click="openMenu = !openMenu" class="bg-gray-200 hover:bg-gray-300 p-1.5 rounded-full transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                                                <circle cx="5" cy="12" r="2" /><circle cx="12" cy="12" r="2" /><circle cx="19" cy="12" r="2" />
                                            </svg>
                                        </button>
                                        <div x-show="openMenu" @click.away="openMenu = false" class="absolute left-0 mt-2 w-40 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden z-50">
                                            ${editBtn}
                                            ${deleteBtn}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                            });

                            Alpine.initTree(document.getElementById('clientTableBody'));
                            bindDeleteButtons();
                        }
                    });
                }, 400);
            });

            function bindDeleteButtons() {
                document.querySelectorAll('.delete-btn').forEach(button => {
                    if (button.dataset.bound) return;
                    button.dataset.bound = '1';

                    button.addEventListener('click', function(e) {
                        e.preventDefault();
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
            }
            bindDeleteButtons();

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

    <script>
        function editclientModal() {
            return {
                open: false,
                id: null,
                name: '',
                phone: '',
                type: '',
                updateUrl: '',

                init() {
                    window.addEventListener('edit-client', event => {
                        this.id = event.detail.id;
                        this.name = event.detail.name;
                        this.type = event.detail.type;
                        this.phone = event.detail.phone;

                        this.updateUrl = "/client/" + this.id;

                        this.open = true; // يفتح المودال فقط عند الضغط على زر تعديل
                    });
                }
            }
        }
    </script>
@endsection
