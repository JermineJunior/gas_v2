<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>حسابات العميل — {{ $client->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
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
            background: #1e40af;
            color: white;
            font-weight: bold;
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
    @include('partials.theme')>

    <!-- شريط علوي فيه اللوجو -->
    <div class="top-bar">
        <div></div> <!-- فارغ عشان التوازن -->
        <img src="{{ public_path('images/logo.png') }}" alt="Al Fajr Logo" class="logo">
    </div>

    <div class="header">
        <h1>تقرير حسابات العميل</h1>
        <p>اسم العميل: <strong>{{ $client->name }}</strong></p>
    </div>

    <div class="client-info">
        <strong>الهاتف:</strong> {{ $client->phone ?? 'لا يوجد' }}<br>
        <strong>عدد العمليات:</strong> {{ $client->accounts->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>التاريخ</th>
                <th>التفاصيل</th>
                <th>عليه</th>
                <th>له</th>
                <th>الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @forelse($client->accounts as $account)
                <tr style="color: {{ $account->type == 0 ? '#065f46' : '#991b1b' }};">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $account->date->format('Y-m-d') }}</td>
                    <td>{{ $account->note }}</td>
                    <td>{{ number_format($account->type == 1 ? $account->amount : 0) }} ج.س</td>
                    <td>{{ number_format($account->type == 0 ? $account->amount : 0) }} ج.س</td>
                    <td class="{{ $account->total > 0 ? 'text-red' : 'text-green' }}">{{ number_format($account->total) }} ج.س</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">لا توجد عمليات حاليا لدى هذا العميل</td>
                </tr>
            @endforelse

            <tr class="summary-row">
                <td colspan="3">إجمالي العمليات</td>
                <td class="text-red">{{ number_format($client->accounts()->where('type', 1)->sum('amount')) }} ج.س</td>
                <td class="text-green">{{ number_format($client->accounts()->where('type', 0)->sum('amount')) }} ج.س</td>
                <td></td>
            </tr>

            @php
                $total = $client->accounts()->where('type', 1)->sum('amount') - $client->accounts()->where('type', 0)->sum('amount');
            @endphp

            <tr class="total-row">
                <td colspan="3">الرصيد الإجمالي — {{ $total > 0 ? 'عليه' : 'له' }}</td>
                <td colspan="3" class="{{ $total > 0 ? 'bg-red' : 'bg-green' }}">{{ number_format(abs($total)) }} ج.س</td>
            </tr>
        </tbody>
    </table>

</body>

</html>
