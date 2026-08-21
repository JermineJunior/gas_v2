@extends('layouts.app')

@section('title', 'العملاء')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('content')
    <!-- قسم الحسابات -->
    <div x-data="{ showAddModal: false, showEditModal: false, editclient: { id: '', name: '', phone: '', type: '' }, activeTab: 'all' }" x-init="$watch('activeTab', v => window._clientActiveTab = v); window._clientActiveTab = activeTab" class="max-w-5xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">

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

        <!-- بطاقات الحسابات -->
        <div id="clientGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($clients as $client)
                <div data-type="{{ $client->type }}" x-show="activeTab === 'all' || activeTab == '{{ $client->type }}'"
                    class="relative bg-gray-50 border border-gray-200 rounded-2xl shadow-sm p-5 flex flex-col justify-between">

                    <!-- زر ثلاث نقاط + قائمة منسدلة -->
                    <div x-data="{ openMenu: false }" class="absolute top-3 left-3">
                        <button @click="openMenu = !openMenu"
                            class="bg-gray-200 hover:bg-gray-300 p-2 rounded-full transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" viewBox="0 0 24 24"
                                fill="currentColor">
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

                    <!-- اسم + الرصيد -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $client->name }}</h3>
                        <p class="text-sm text-gray-600 mb-1">الرصيد الإجمالي:</p>
                        <p
                            class="text-2xl font-bold {{ $client->details()->sum('total') - $client->details()->sum('amount') >= 0 ? 'text-primary-strong' : 'text-red-600' }} mb-3">
                            {{ number_format($client->details()->sum('total') - $client->details()->sum('amount')) }}
                            ج.س
                        </p>
                    </div>

                    <!-- الأزرار -->
                    <div class="flex justify-between gap-2 mt-4">

                        <!-- زر المديونيات -->
                        <a href="{{ route('client.show', $client->id) }}"
                            class="w-1/2 text-center bg-primary-strong text-white py-2 rounded-lg hover:bg-primary-strong transition font-semibold">
                            المديونيات
                        </a>

                        <!-- زر الإيرادات -->
                        <a href="{{ route('revenue.index', $client->id) }}"
                            class="w-1/2 text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                            الإيرادات
                        </a>
                    </div>
                </div>
            @endforeach
        </div>


        @if ($clients->isEmpty())
            <p class="text-center text-gray-600 mt-6">لا يوجد عملاء مسجلة حالياً.</p>
        @endif

        <!-- 🟦 مودال إضافة عميل جديد -->
        <div x-show="showAddModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
                            $("#clientGrid").empty();

                            let filtered = activeTab === 'all' ? response : response.filter(c => String(c.type) === String(activeTab));

                            if (filtered.length === 0) {
                                $("#clientGrid").html(
                                    '<p class="text-center col-span-3 text-gray-600 mt-6">لا توجد نتائج مطابقة.</p>'
                                );
                                return;
                            }

                            filtered.forEach(client => {

                                let balance = (client.total_sum - client
                                    .paid_sum) || 0;
                                balance = balance.toLocaleString();

                                let revenueBtn = `
                                        <a href="/revenue/${client.id}"
                                            class="w-1/2 text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                                            الإيرادات
                                        </a>
                                    `;

                                let deleteBtn = '';
                                @can('clients.delete')
                                deleteBtn = `
                                        <form action="/client/delete/${client.id}" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full text-right px-4 py-2 hover:bg-gray-100 text-red-600">
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

                                $("#clientGrid").append(`
                        <div data-type="${client.type}" x-show="activeTab === 'all' || activeTab == '${client.type}'" class="relative bg-gray-50 border border-gray-200 rounded-2xl shadow-sm p-5 flex flex-col justify-between">

                            <div x-data="{ openMenu: false }" class="absolute top-3 left-3">
                                <button @click="openMenu = !openMenu" class="bg-gray-200 hover:bg-gray-300 p-2 rounded-full transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="5" cy="12" r="2" /><circle cx="12" cy="12" r="2" /><circle cx="19" cy="12" r="2" />
                                    </svg>
                                </button>
                                <div x-show="openMenu" @click.away="openMenu = false" class="absolute left-0 mt-2 w-40 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden z-50">
                                    ${editBtn}
                                    ${deleteBtn}
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">${client.name}</h3>
                                <p class="text-sm text-gray-600 mb-1">الرصيد الإجمالي:</p>
                                <p class="text-2xl font-bold text-primary-strong mb-3">
                                    ${balance} ج.س
                                </p>
                            </div>

                            <div class="flex justify-between gap-2 mt-4">
                                <a href="/client/${client.id}"
                                    class="w-1/2 text-center bg-primary-strong text-white py-2 rounded-lg hover:bg-primary-strong transition font-semibold">
                                    المديونيات
                                </a>
                                ${revenueBtn}
                            </div>

                        </div>
                    `);
                            });

                            Alpine.initTree(document.getElementById('clientGrid'));
                        }
                    });
                }, 400);
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
