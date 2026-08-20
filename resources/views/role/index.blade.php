<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأدوار</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    @include('header')

    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">إدارة الأدوار والصلاحيات</h2>
            @can('roles.create')
                <a href="{{ route('roles.create') }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    + إضافة دور جديد
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead class="bg-green-600 text-white text-sm">
                    <tr>
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">اسم الدور</th>
                        <th class="p-3 text-center">عدد الصلاحيات</th>
                        <th class="p-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr class="border-b">
                            <td class="p-3 text-center">{{ $loop->iteration }}</td>
                            <td class="p-3 text-center font-semibold">{{ $role->name }}</td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-700 font-semibold">
                                    {{ $role->permissions_count }} صلاحية
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @can('roles.edit')
                                        <a href="{{ route('roles.edit', $role) }}"
                                            class="text-green-600 hover:text-green-800">تعديل</a>
                                    @endcan
                                    @can('roles.delete')
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">حذف</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">لا توجد أدوار</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
</body>

</html>
