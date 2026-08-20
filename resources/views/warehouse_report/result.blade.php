<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تقرير المستودع - {{ $warehouse->name }}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: none !important; }
            .print-inline { display: flex !important; justify-content: space-between; gap: 20px; }
            .print-inline > div { flex: 1; text-align: center; }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6 print-card">
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="شعار" class="w-16 h-16 object-contain">
                <h2 class="text-2xl font-bold text-gray-700">تقرير المستودع - {{ $warehouse->name }}</h2>
            </div>
            <div class="no-print flex gap-3">
                <a href="{{ route('reports.warehouse') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600 transition">
                    رجوع
                </a>
                <button onclick="window.print()"
                    class="bg-primary-strong text-white px-4 py-2 rounded-lg shadow hover:bg-primary-strong transition">
                    طباعة
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-primary/10 p-4 rounded-lg print-inline">
            <div>
                <span class="block text-sm text-gray-600">المستودع:</span>
                <p class="font-semibold">{{ $warehouse->name }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">نوع الوقود:</span>
                <p class="font-semibold">{{ $fuelType == 1 ? 'جازولين' : 'بنزين' }}</p>
            </div>
            <div>
                <span class="block text-sm text-gray-600">من / الى:</span>
                <p class="font-semibold">{{ $startDate ?? 'من البداية' }} - {{ $endDate ?? 'حتى الآن' }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                <thead class="bg-primary-strong text-white text-sm">
                    <tr>
                        <th class="p-3 text-center">#</th>
                        <th class="p-3 text-center">التاريخ</th>
                        <th class="p-3 text-center">النوع</th>
                        <th class="p-3 text-center">الكمية</th>
                        <th class="p-3 text-center">الرصيد</th>
                        <th class="p-3 text-center">المصدر / الوجهة</th>
                        <th class="p-3 text-center">ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $t)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-primary/5 transition">
                            <td class="p-3 text-center">{{ $index + 1 }}</td>
                            <td class="p-3 text-center">{{ $t->date->format('Y/m/d') }}</td>
                            <td class="p-3 text-center">
                                @if($t->type == \App\Models\WarehouseTransaction::TYPE_ADDITION)
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">اضافة</span>
                                @elseif($t->type == \App\Models\WarehouseTransaction::TYPE_WITHDRAWAL)
                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">سحب</span>
                                @elseif($t->type == \App\Models\WarehouseTransaction::TYPE_TRANSFER_OUT)
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">تحويل صادر</span>
                                @elseif($t->type == \App\Models\WarehouseTransaction::TYPE_TRANSFER_IN)
                                    <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">تحويل وارد</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if(in_array($t->type, [\App\Models\WarehouseTransaction::TYPE_ADDITION, \App\Models\WarehouseTransaction::TYPE_TRANSFER_IN]))
                                    <span class="text-green-700 font-semibold">+{{ formatNumber($t->quantity) }}</span>
                                @else
                                    <span class="text-red-700 font-semibold">-{{ formatNumber($t->quantity) }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-semibold">{{ formatNumber($t->balance_after ?? 0) }}</td>
                            <td class="p-3 text-center">
                                @if($t->type == \App\Models\WarehouseTransaction::TYPE_WITHDRAWAL)
                                    {{ $t->withdrawal->driver_name ?? '' }} {{ $t->withdrawal->car_number ? '(' . $t->withdrawal->car_number . ')' : '' }}
                                @elseif(in_array($t->type, [\App\Models\WarehouseTransaction::TYPE_TRANSFER_OUT, \App\Models\WarehouseTransaction::TYPE_TRANSFER_IN]))
                                    {{ $t->relatedTransaction->warehouse->name ?? '-' }}
                                @else
                                    {{ $t->source ?? '-' }}
                                @endif
                            </td>
                            <td class="p-3 text-center">{{ $t->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">لا توجد حركات في الفترة المحددة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->count() > 0)
            <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">الرصيد الحالي:</span>
                <span class="text-xl font-bold text-primary-strong">{{ formatNumber($currentBalance) }}</span>
            </div>
        @endif
    </div>
</body>
</html>
