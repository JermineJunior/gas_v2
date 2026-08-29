<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام تسجيل بيانات الوقود')</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body, .font-sans {
            font-family: 'Cairo', sans-serif !important;
        }
    </style>

    @yield('styles')
</head>

<body class="@yield('body-class', 'bg-gray-100 min-h-screen p-6')">

    @hasSection('no_header')
    @else
        @include('header')

        @auth
            @unless (request()->routeIs('login', 'register', 'password.*', 'verification.*', 'stations.hub', 'warehouses.create', 'warehouses.edit', 'warehouse_withdrawals.create', 'warehouse_withdrawals.edit', 'warehouse_transactions.create', 'warehouse_transactions.edit', 'warehouse_transfers.create', 'roles.create', 'roles.edit'))
                <div class="max-w-6xl mx-auto mb-2">
                    <a href="#"
                        onclick="event.preventDefault(); var p = document.referrer; if (p && p !== location.href) { window.location.href = p; return; } history.go(-1);"
                        class="text-gray-400 hover:text-gray-600 text-sm">←&nbsp;رجوع</a>
                </div>
            @endunless
        @endauth
    @endif

    @yield('content')

    {{-- مودال الملف التعريفي (متاح لكل مستخدم مسجل) --}}
    <div x-data="{ show: false }" x-cloak x-on:open-modal.window="if($event.detail.id === 'profileModal') show = true"
        x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        style="display: none;" x-transition>
        <div @click.away="show = false" class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">الملف التعريفي</h2>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-1">اسم المستخدم</label>
                <input type="text" value="{{ auth()->user()->name }}" readonly
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
            </div>

            <form action="{{ route('user.update-password') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">كلمة السر القديمة</label>
                        <input type="password" name="old_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">كلمة السر الجديدة</label>
                        <input type="password" name="new_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">تأكيد كلمة السر</label>
                        <input type="password" name="new_password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:ring">
                    </div>
                </div>

                <div class="flex justify-end mt-4 gap-2">
                    <button type="button" @click="show = false"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                        إغلاق
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-primary-strong text-white">
                        حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>

    @yield('scripts')

    @hasSection('no_header')
    @else
        @include('messages')
    @endif
</body>

</html>
