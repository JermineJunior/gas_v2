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
    @endif

    @yield('content')

    @hasSection('no_header')
    @else
        @include('messages')
    @endif

    @yield('scripts')
</body>

</html>
