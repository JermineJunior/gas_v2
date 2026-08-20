<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>اضافة مستودع</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    @include('header')

    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">اضافة مستودع جديد</h2>
            <a href="{{ route('warehouses.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">رجوع</a>
        </div>

        <form action="{{ route('warehouses.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">اسم المستودع</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition">
                    حفظ
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @include('messages')
</body>
</html>
