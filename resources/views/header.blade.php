@include('partials.theme')

<style>
    .nav-link {
        position: relative;
        padding: 0.35rem 0.5rem;
        border-bottom: 2px solid transparent;
        border-radius: 0.5rem;
        color: var(--color-text-muted);
        font-weight: 500;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }

    .nav-link:hover {
        color: var(--color-primary);
        background-color: var(--color-surface-2);
    }

    .nav-link.active {
        color: var(--color-heading);
        font-weight: 700;
        border-bottom-color: var(--color-accent);
    }

    .nav-link + .nav-link::before {
        content: "";
        position: absolute;
        top: 50%;
        inset-inline-start: -0.75rem;
        transform: translateY(-50%);
        width: 1px;
        height: 1.25rem;
        background-color: var(--color-divider);
    }

    .logout-btn {
        background-color: var(--color-surface);
        color: var(--color-primary);
        border: 1px solid var(--color-divider);
        transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }

    .logout-btn:hover {
        background-color: var(--color-surface-2);
        color: var(--color-primary-strong);
    }

    .mobile-logout-btn {
        background-color: var(--color-primary);
        color: #fff;
        transition: background-color .2s ease;
    }

    .mobile-logout-btn:hover {
        background-color: var(--color-primary-strong);
    }

    .mobile-link {
        display: block;
        padding: 0.4rem 0.5rem;
        border-radius: 0.5rem;
        color: var(--color-text);
        transition: background-color .2s ease, color .2s ease;
    }

    .mobile-link:hover {
        color: var(--color-primary);
        background-color: var(--color-surface-2);
    }

    .mobile-link.active {
        color: var(--color-primary);
        font-weight: 700;
    }
</style>

@php
    $isHome = request()->routeIs('station.*');
    $isUsers = request()->routeIs('user.*');
    $isSuppliers = request()->routeIs('supplier.*');
    $isPrice = request()->routeIs('price.*');
    $isAccounts = request()->routeIs('client.*');
    $isReports = request()->routeIs('reports.*');
@endphp

<div class="max-w-6xl mx-auto mb-6">
    <nav class="flex items-center justify-between px-4 py-2 rounded-lg">
        <!-- جهة الشمال (في RTL تظهر على اليسار) -->
        <div class="flex items-center gap-3 relative" x-data="{ open: false }">
            <!-- الشعار + اسم النظام -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="شعار" class="w-10 h-10 object-contain">
                <span class="font-semibold text-gray-700">نظام تسجيل بيانات الوقود</span>
            </div>

            <!-- زر صغير لفتح القائمة -->
            <button @click="open = !open"
                class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 focus:outline-none transition"
                title="القائمة">
                <span class="text-gray-600 text-xl font-bold">⋮</span>
            </button>

            <!-- القائمة المنسدلة -->
            <div x-cloak x-show="open" @click.away="open = false"
                class="absolute top-full right-0 mt-2 w-52 bg-white border rounded-lg shadow-lg py-2 z-50">
                <div class="px-4 py-2 border-b text-gray-700 font-semibold">
                    {{ auth()->user()->name }}
                </div>
                <button @click="$dispatch('open-modal', { id: 'profileModal' }); open = false"
                    class="block w-full text-right px-4 py-2 text-gray-700 hover:bg-gray-100">
                    الملف التعريفي
                </button>
            </div>
        </div>


        <!-- روابط -->
        <div class="hidden md:flex items-center gap-6">
            <a href="{{ route('station.index') }}" class="nav-link {{ $isHome ? 'active' : '' }}">الرئيسية</a>
            @if (auth()->user()->type == 0 || auth()->user()->type == 1 || auth()->user()->type == 2)
                <a href="{{ route('user.index') }}" class="nav-link {{ $isUsers ? 'active' : '' }}">المستخدمين</a>
                <a href="{{ route('supplier.index') }}" class="nav-link {{ $isSuppliers ? 'active' : '' }}">الموردين</a>
            @endif
            <a href="{{ route('price.create') }}" class="nav-link {{ $isPrice ? 'active' : '' }}">تغيير الاسعار</a>
            <a href="{{ route('client.index') }}" class="nav-link {{ $isAccounts ? 'active' : '' }}">ادراة الحسابات</a>

            <div x-data="{ open: false }" class="nav-link {{ $isReports ? 'active' : '' }}">
                <button @click="open = !open" class="flex items-center gap-1 focus:outline-none">
                    التقارير
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-cloak x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg py-2 z-50">
                    <a href="{{ route('reports.debt') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير الحسابات</a>
                    <a href="{{ route('reports.tuncker') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير التناكر</a>
                    <a href="{{ route('reports.machine_detail') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير العدادات</a>
                    <a href="{{ route('reports.deposit_detail') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير التوريد</a>
                    <a href="{{ route('reports.supplier') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير الموردين</a>
                </div>
            </div>
        </div>

        <!-- زر تسجيل الخروج خفيف -->
        <div class="flex items-center gap-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="hidden md:inline-block logout-btn px-3 py-1 rounded-lg shadow-sm">
                    تسجيل الخروج
                </button>
            </form>

            <!-- زر اختصار للموبايل -->
            <button id="mobileMenuBtn" class="md:hidden text-gray-600" aria-label="قائمة">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </nav>

    <!-- قائمة الموبايل -->
    <div id="mobileMenu" class="md:hidden hidden bg-white rounded-lg shadow mt-2 p-4 space-y-3">
        <a href="{{ route('station.index') }}" class="mobile-link {{ $isHome ? 'active' : '' }}">الرئيسية</a>
        @if (auth()->user()->type == 0 || auth()->user()->type == 1 || auth()->user()->type == 2)
            <a href="{{ route('user.index') }}" class="mobile-link {{ $isUsers ? 'active' : '' }}">المستخدمين</a>
            <a href="{{ route('supplier.index') }}" class="mobile-link {{ $isSuppliers ? 'active' : '' }}">الموردين</a>
        @endif
        <a href="{{ route('price.create') }}" class="mobile-link {{ $isPrice ? 'active' : '' }}">تغيير الاسعار</a>
        <a href="{{ route('client.index') }}" class="mobile-link {{ $isAccounts ? 'active' : '' }}">ادراة الحسابات</a>

        <!-- Dropdown للتقارير -->
        <div x-data="{ open: false }" class="border rounded-lg">
            <button @click="open = !open" class="w-full flex items-center justify-between mobile-link px-4 py-2">
                التقارير
                <svg class="w-4 h-4 ml-2 transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-cloak x-show="open" class="mt-1 space-y-1 bg-gray-50 rounded-lg shadow-inner">
                <a href="{{ route('reports.debt') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير الحسابات</a>
                <a href="{{ route('reports.tuncker') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير التناكر</a>
                <a href="{{ route('reports.machine_detail') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير العدادات</a>
                <a href="{{ route('reports.deposit_detail') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير التوريد</a>
                <a href="{{ route('reports.supplier') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">تقرير الموردين</a>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full text-left mobile-logout-btn px-3 py-2 rounded-lg" type="submit">
                تسجيل الخروج
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
