<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>حسابات العميل — {{ $client->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'cairo', sans-serif;
            direction: rtl;
            text-align: right;
            background: #fff;
            margin: 40px;
        }

        h1, h2, h3, h4 {
            margin: 0;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 110px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #1d4ed8;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            color: #333;
            font-size: 16px;
        }

        .client-info {
            background: #e8f7ff;
            border: 1px solid #00AEEF;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 15px;
            line-height: 1.7;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
        }

        th {
            background: #1d4ed8;
            color: white;
            font-weight: 600;
            text-align: center;
        }

        td {
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        tr:hover td {
            background: #eef2ff;
        }

        .text-green {
            color: #065f46;
            font-weight: 600;
        }

        .text-red {
            color: #991b1b;
            font-weight: 600;
        }

        .bg-red {
            background: #991b1b;
            font-weight: bold;
        }

        .bg-green {
            background: #065f46;
            font-weight: bold;
        }

        .summary-row {
            background: #f3f4f6;
            font-weight: bold;
        }
        
        .total-row {
            background: #5a78db;
            color: white;
            font-weight: bold;
        }
    
        .total-row td {
           color: #e8f7ff
    }


        @media print {
            body {
                margin: 20px;
            }

            .logo {
                width: 100px;
            }

            .top-bar {
                margin-bottom: 10px;
            }

            .header h1 {
                color: #000;
            }
        }
    </style>
</head>

<body>

    <!-- شريط علوي فيه اللوجو -->
    <div class="top-bar">
        <div></div> <!-- فارغ عشان التوازن -->
        <img src="{{ public_path('images/logo.png') }}" alt="Al Fajr Logo" class="logo">
    </div>

    <div class="header">
        <h1>تقرير حسابات العميل</h1>
        <p>اسم العميل: <strong>{{ $client->name }}</strong></p>
    </div>

    @if (!empty($message))
        <div class="client-info" style="background:#fffbeb; border-color:#f59e0b;">
            <strong>رسالة:</strong> {{ $message }}
        </div>
    @endif

    <div class="client-info">
        <strong>الهاتف:</strong> {{ $client->phone ?? 'لا يوجد' }}<br>
        <strong>النوع:</strong> {{ $client->type == 1 ? 'عميل' : 'باص' }}<br>
        <strong>عدد العمليات:</strong> {{ $rows->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>التاريخ</th>
                <th>البيان</th>
                <th>عدد اللترات</th>
                <th>سعر اللتر</th>
                <th>عليه</th>
                <th>له</th>
                <th>الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php $account = $row['detail']; $balance = $row['balance']; @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $account->date->format('Y-m-d') }}</td>
                    <td>{{ $account->note ?? '-' }}</td>
                    <td>{{ formatNumber($account->liter) }}</td>
                    <td>{{ formatNumber($account->price) }}</td>
                    <td class="{{ $account->total > 0 ? 'text-red' : '' }}">
                        {{ $account->total > 0 ? number_format($account->total) . ' ج.س' : '-' }}</td>
                    <td class="{{ $account->amount > 0 ? 'text-green' : '' }}">
                        {{ $account->amount > 0 ? number_format($account->amount) . ' ج.س' : '-' }}</td>
                    <td class="{{ $balance > 0 ? 'text-red' : 'text-green' }}">{{ number_format($balance) }} ج.س</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">لا توجد عمليات حاليا لدى هذا العميل</td>
                </tr>
            @endforelse

            <tr class="summary-row">
                <td colspan="3">إجمالي العمليات</td>
                <td></td>
                <td></td>
                <td class="text-red">{{ number_format($totalDebit) }} ج.س</td>
                <td class="text-green">{{ number_format($totalCredit) }} ج.س</td>
                <td></td>
            </tr>

            <tr class="total-row">
                <td colspan="6">الرصيد الإجمالي — {{ $total > 0 ? 'عليه' : 'له' }}</td>
                <td colspan="2">{{ number_format(abs($total)) }} ج.س</td>
            </tr>
        </tbody>
    </table>

</body>

</html>
