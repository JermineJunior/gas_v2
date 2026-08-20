<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تقرير عملية</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- استدعاء خط عربي (Cairo) -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        @media print {
            button {
                display: none !important;
            }

            /* إزالة الشادو فقط */
            .no-print-shadow {
                box-shadow: none !important;
            }

            /* تثبيت عدد الأعمدة */
            .print-grid-3 {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1rem !important;
            }

            .print-grid-2 {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem !important;
            }

            body {
                background: white !important;
                font-family: 'Cairo', sans-serif !important;

            }
        }

        /* ألوان جديدة للعرض */
        .section-bg {
            background-color: #f0f8ff;
            /* أزرق فاتح هادي */
        }

        .summary-bg {
            background: linear-gradient(to right, #2563eb, #0ea5e9) !important;
            /* أزرق رسمي */
            color: white !important;
        }
    </style>

</head>

<body class="bg-gray-100 font-sans">

    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6 no-print-shadow">

        <!-- زر الطباعة -->
        <div class="flex justify-end mb-4">
            <button onclick="window.print()"
                class="bg-primary hover:bg-primary-strong text-white px-6 py-2 rounded-lg shadow">
                طباعة
            </button>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <!-- صورة المؤسسة -->
                <img src="{{ asset('images/logo.png') }}" alt="شعار المؤسسة" class="w-16 h-16 object-contain mr-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $operation->station->name }}</h1>
                    <p class="text-gray-600">تقرير بيانات الوقود</p>
                </div>
            </div>
        </div>

        <!-- معلومات عامة -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 section-bg rounded-lg print-grid-3">
            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">التاريخ</span>
                <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                    {{ $operation->date }}
                </div>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">رقم التنكر</span>
                <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                    {{ $operation->tuncker_no }}
                </div>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">اسم السائق</span>
                <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                    {{ $operation->driver_name }}
                </div>
            </div>
        </div>

        <!-- التكلفة -->
        @if (auth()->user()->type == 1)
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">التكلفة</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 section-bg rounded-lg print-grid-3">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">شراء جازولين</span>
                        <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                            {{ number_format($operation->buy_diesel) }}</div>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">ترحيل جازولين</span>
                        <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                            {{ number_format($operation->transfer_diesel) }}</div>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">الإجمالي</span>
                        <div class="p-2 border border-gray-300 rounded-lg bg-gray-100">
                            {{ number_format($operation->cost_total) }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6">
            <!-- الفواتير -->
            @forelse ($operation->fuel_details as $fuel)
                <div id="fuelInvoicesContainer" class="space-y-4">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-6 gap-4 p-4 bg-primary-soft rounded-lg relative">

                        <!-- الماكينات + زر الإضافة -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينة</label>
                            <div class="flex items-center gap-2">
                                @if (auth()->user()->type == 3 && $operation->status == 0)
                                    <button type="button"
                                        class="add-machine-btn flex items-center justify-center bg-primary-strong text-white rounded-lg p-2 hover:bg-primary-strong transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                @endif
                                <select name="machine_id[{{ $loop->index }}]"
                                    {{ auth()->user()->type == 1 || $operation->status == 1 ? 'disabled' : '' }}
                                    class="machine w-full p-2 border border-gray-300 rounded-lg select2 focus:ring-2 focus:ring-primary">
                                    <option value="">اختر الماكينة</option>
                                    @foreach ($machines as $machine)
                                        <option @selected($machine->id == $fuel->machine_id) value="{{ $machine->id }}">
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if (auth()->user()->type == 1 || $operation->status == 1)
                                    <input type="hidden" name="machine_id[{{ $loop->index }}]"
                                        value="{{ $fuel->machine_id }}">
                                @endif

                            </div>
                        </div>

                        <!-- عداد البداية -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد البداية</label>
                            <input type="text" name="start_counter[{{ $loop->index }}]" placeholder="0" readonly
                                class="start-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ number_format($fuel->start_counter) }}">
                        </div>

                        <!-- عداد النهاية -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد النهاية</label>
                            <input type="text" name="end_counter[{{ $loop->index }}]" placeholder="0" readonly
                                class="end-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ number_format($fuel->end_counter) }}">
                        </div>

                        <!-- الصافي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">صافي اللتر</label>
                            <input type="text" name="net[{{ $loop->index }}]" readonly
                                class="net w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                                value="{{ number_format($fuel->net) }}">
                        </div>

                        <!-- السعر -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">السعر</label>
                            <input type="text" name="price[{{ $loop->index }}]" placeholder="0.00" readonly
                                class="price w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ number_format($fuel->price) }}">
                        </div>

                        <!-- الإجمالي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الإجمالي</label>
                            <input type="text" name="total[{{ $loop->index }}]" readonly
                                class="total w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                                value="{{ number_format($fuel->total) }}">
                        </div>
                    </div>

                </div>
            @empty
                <div id="fuelInvoicesContainer" class="space-y-4">
                    <div class="fuel-item grid grid-cols-1 md:grid-cols-6 gap-4 p-4 bg-primary-soft rounded-lg relative">

                        <!-- الماكينات + زر الإضافة -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الماكينة</label>
                            <div class="flex items-center gap-2">
                                @if (auth()->user()->type == 3 && $operation->status == 0)
                                    <button type="button"
                                        class="add-machine-btn flex items-center justify-center bg-primary-strong text-white rounded-lg p-2 hover:bg-primary-strong transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                @endif
                                <select name="machine_id[0]"
                                    {{ auth()->user()->type == 1 || $operation->status == 1 ? 'disabled' : '' }}
                                    class="machine w-full p-2 border border-gray-300 rounded-lg select2 focus:ring-2 focus:ring-primary">
                                    <option value="">اختر الماكينة</option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if (auth()->user()->type == 1 || $operation->status == 1)
                                    <input type="hidden" name="machine_id[0]">
                                @endif
                            </div>
                        </div>

                        <!-- عداد البداية -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد البداية</label>
                            <input type="text" name="start_counter[0]" placeholder="0" readonly
                                class="start-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- عداد النهاية -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">عداد النهاية</label>
                            <input type="text" name="end_counter[0]" placeholder="0" readonly
                                class="end-counter w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- الصافي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">صافي اللتر</label>
                            <input type="text" name="net[0]" readonly
                                class="net w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        <!-- السعر -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">السعر</label>
                            <input type="text" name="price[0]" placeholder="0.00" readonly
                                class="price w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- الإجمالي -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">الإجمالي</label>
                            <input type="text" name="total[0]" readonly
                                class="total w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>
                    </div>

                </div>
            @endforelse

            <!-- الإجماليات -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">إجمالي اللترات</label>
                    <input type="text" id="grandLiters" readonly name="grand_litters"
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                        value="{{ number_format($operation->grand_litters) }}">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">إجمالي المبلغ</label>
                    <input type="text" id="grandTotal" readonly name="grand_total"
                        value="{{ number_format($operation->grand_total) }}"
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>



            <!-- الخصم والصافي -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">خصم العمولة</label>
                    <input type="text" id="discount" name="discount" readonly
                        value="{{ number_format($operation->discount) }}"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">الصافي</label>
                    <input type="text" id="netTotal" readonly name="net_total"
                        value="{{ number_format($operation->grand_total - $operation->discount) }}"
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>
        </div>

        <!-- المصروفات -->
        <div id="expenseContainer" class="space-y-3">
            <!-- الصف الأساسي -->
            @forelse ($operation->expense_details as $expense)
                <div class="grid gap-3 items-start relative grid-cols-[1fr,2fr]">
                    <div>
                        <input type="text" name="expense_amount[{{ $loop->index }}]" placeholder="المبلغ"
                            readonly
                            class="expense-amount w-full p-2 border border-gray-300 rounded-lg  focus:ring-2 focus:ring-primary"
                            value="{{ number_format($expense->expense_amount) }}">
                    </div>
                    <div>
                        <input type="text" placeholder="البيان" name="expense_desc[{{ $loop->index }}]"
                            class="w-full p-2 border border-gray-300 rounded-lg  focus:ring-2 focus:ring-primary"
                            value="{{ $expense->expense_desc }}" readonly>
                    </div>
                </div>
            @empty
                <div class="grid gap-3 items-start relative grid-cols-[1fr,2fr]">
                    <div>
                        <input type="text" name="expense_amount[0]" placeholder="المبلغ" readonly
                            class="expense-amount w-full p-2 border border-gray-300 rounded-lg  focus:ring-2 focus:ring-primary"
                            value="">
                    </div>
                    <div>
                        <input type="text" placeholder="البيان" name="expense_desc[0]"
                            class="w-full p-2 border border-gray-300 rounded-lg  focus:ring-2 focus:ring-primary"
                            readonly>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">بنود التوريد</h2>
            </div>

            <div id="depositContainer" class="space-y-3">
                @forelse ($operation->deposit_details as $deposit)
                    <div
                        class="deposit-item grid grid-cols-[1fr,2fr] gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                        <!-- مبلغ التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ التوريد</label>
                            <input type="text" name="deposit_amount[{{ $loop->index }}]"
                                id="deposit_amount_{{ $loop->index }}" placeholder="0.00"
                                {{ auth()->user()->type == 1 || $operation->statsu == 1 ? 'readonly' : '' }}
                                class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ number_format($deposit->deposit_amount) }}">

                        </div>

                        <!-- بيان التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">بيان التوريد</label>
                            <input type="text" name="deposit_desc[{{ $loop->index }}]"
                                id="deposit_desc_{{ $loop->index }}" placeholder="مثال: توريد يوم الأحد" readonly
                                class="deposit-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="{{ $deposit->deposit_desc }}">
                        </div>
                    </div>
                @empty
                    <div
                        class="deposit-item grid grid-cols-[1fr,2fr] gap-3 items-start p-4 bg-primary-soft rounded-lg relative">
                        <!-- مبلغ التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ التوريد</label>
                            <input type="text" name="deposit_amount[0]" id="deposit_amount_0" placeholder="0.00"
                                {{ auth()->user()->type == 1 || $operation->statsu == 1 ? 'readonly' : '' }}
                                class="deposit-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                value="0">
                        </div>

                        <!-- بيان التوريد -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">بيان التوريد</label>
                            <input type="text" name="deposit_desc[0]" id="deposit_desc_0"
                                placeholder="مثال: توريد يوم الأحد" readonly
                                class="deposit-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        <!-- الملاحظات -->
        <div class="mt-6">
            <label class="block mb-1 text-sm font-medium text-gray-700">ملاحظات</label>
            <textarea rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                readonly>{{ $operation->note }}</textarea>
        </div>

        <hr>
        <!-- التصفية -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">التصفية</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">مبلغ التصفية</label>
                    <input id="settlement" type="text" name="sett_lement"
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly
                        value="{{ number_format($operation->net_total) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المطلوب من مدير
                        المحطة</label>
                    <input type="text" id="remaining" readonly name="remaining"
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100"
                        value="{{ number_format($operation->remaining) }}">

                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                    <div id="status"
                        class="w-full p-2 border border-gray-300 rounded-lg font-medium text-primary bg-primary-soft">
                        @if (
                            $operation->grand_total -
                                $operation->expense_details()->sum('expense_amount') -
                                $operation->deposit_details()->sum('deposit_amount') ==
                                0)
                            خالص
                        @elseif (
                            $operation->grand_total -
                                $operation->expense_details()->sum('expense_amount') -
                                $operation->deposit_details()->sum('deposit_amount') >
                                0)
                            مبلغ لم يستلم
                        @else
                            له
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (auth()->user()->type == 1)
            <!-- ملخص الفاتورة -->
            <div class="bg-gradient-to-r bg-primary-strong text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold mb-4 text-center">ملخص الفاتورة</h3>
                <div class="space-y-2">

                    <!-- الصافي -->
                    <div class="flex justify-between">
                        <span>صافي تفاصيل البيع :</span>
                        <span id="summaryNet">{{ number_format($operation->net_total) }} جنيه</span>
                    </div>

                    <!-- مبلغ التكلفة -->
                    <div class="flex justify-between">
                        <span>مبلغ التكلفة:</span>
                        <span id="summaryCost">{{ number_format($operation->cost_total) }} جنيه</span>
                    </div>

                    <!-- مجموع المصروفات -->
                    <div class="flex justify-between">
                        <span>مجموع المصروفات :</span>
                        <span
                            id="summaryexpense">{{ number_format($operation->expense_details()->where('status', 1)->sum('expense_amount')) }}
                            جنيه</span>
                    </div>

                    <!-- مجموع التوريدات -->
                    <div class="flex justify-between">
                        <span>مجموع التوريدات :</span>
                        <span
                            id="summaryexpense">{{ number_format($operation->deposit_details()->sum('deposit_amount')) }}
                            جنيه</span>
                    </div>

                    <!-- الربح -->
                    <div class="border-t border-white/30 pt-2 mt-2">
                        <div class="flex justify-between text-xl font-bold">
                            <span>الربح:</span>
                            <span
                                id="summaryProfit">{{ number_format($operation->net_total - ($operation->expense_details()->where('status', 1)->sum('expense_amount') + $operation->deposit_details()->sum('deposit_amount') + $operation->cost_total)) }}
                                جنيه</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>
