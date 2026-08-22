@extends('layouts.app')

@section('title', $expense->station->name . ' — تعديل المصروفات')
@section('body-class', 'bg-gray-100 p-6')

@section('content')
    @php
        $allApproved = $expense->expense_details->isNotEmpty() && $expense->expense_details->every(fn($d) => $d->status == 1);
    @endphp
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">

        @if ($allApproved)
            <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-lg p-4 text-center font-semibold">
                هذه المصروفات معتمدة بالكامل ولا يمكن تعديلها
            </div>
        @endif

        <form action="{{ route('expense.update', $expense->id) }}" method="POST" id="storeForm">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $expense->station_id }}" name="station_id">

            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-primary-soft rounded-lg relative mb-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">التاريخ</label>
                        <input type="date" name="date" required
                            class="date w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                            value="{{ $expense->date }}" @if($allApproved) readonly @endif>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">الإجمالي</label>
                        <input type="text" id="grandTotal" readonly
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 font-bold">
                    </div>
                </div>

                @if (!$allApproved)
                    @can('expenses.update')
                        <div class="flex justify-end items-center mb-4">
                            <button id="addExpenseBtn" type="button"
                                class="flex items-center bg-primary-strong text-white px-3 py-1 rounded-lg hover:bg-primary-strong">
                                إضافة مصروف
                            </button>
                        </div>
                    @endcan
                @endif

                <div id="expenseContainer" class="space-y-3">
                    @foreach ($expense->expense_details as $detail)
                        @php $isApproved = $detail->status == 1; @endphp
                        <div
                            class="expense-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-primary-soft' }} rounded-lg relative">
                            <input type="hidden" name="detail_ids[]" value="{{ $detail->id }}">

                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ المصروف
                                    @if ($isApproved)
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">معتمد</span>
                                    @endif
                                </label>
                                <input type="text" name="expense_amount[{{ $loop->index }}]" id="expense_amount_{{ $loop->index }}"
                                    placeholder="0.00" value="{{ number_format($detail->expense_amount) }}"
                                    class="expense-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                    @if ($isApproved) readonly @endif>
                                @if ($isApproved && $detail->approver)
                                    <p class="text-xs text-gray-500 mt-1">بواسطة: {{ $detail->approver->name }} — {{ $detail->approved_at?->format('Y/m/d') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">بيان المصروف</label>
                                <input type="text" name="expense_desc[{{ $loop->index }}]" id="expense_desc_{{ $loop->index }}"
                                    placeholder="مثال: صيانة مضخة" value="{{ $detail->expense_desc }}"
                                    class="expense-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                    @if ($isApproved) readonly @endif>
                            </div>

                            @if (!$isApproved)
                                @if ($loop->index != 0)
                                    <button type="button"
                                        class="remove-expense-row text-red-600 font-bold text-lg mt-6">✖</button>
                                @endif
                            @else
                                <span class="text-green-600 font-bold mt-6">✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-start space-x-3 space-x-reverse mt-6">
                @if (!$allApproved)
                    <button type="submit"
                        class="flex items-center bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition-colors">
                        حفظ
                    </button>
                @endif
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')


    <script>
        $(document).ready(function() {

            function formatWithCommas(num) {
                if (num === null || num === '' || isNaN(Number(num))) return '';
                return Number(num).toLocaleString('en-US', {
                    maximumFractionDigits: 2
                });
            }

            function parseNumber(str) {
                let cleaned = String(str || '').replace(/,/g, '');
                let n = parseFloat(cleaned);
                return isNaN(n) ? 0 : n;
            }

            const expenseContainer = document.getElementById('expenseContainer');
            const addExpenseBtn = document.getElementById('addExpenseBtn');
            const storeForm = document.getElementById('storeForm');

            function calculateTotal() {
                let total = 0;
                $('.expense-amount').each(function() {
                    total += parseNumber($(this).val());
                });
                $('#grandTotal').val(formatWithCommas(total));
            }

            $('#storeForm').on('submit', function() {
                this.querySelectorAll('input.expense-amount').forEach(el => {
                    if (el && el.value) el.value = String(el.value).replace(/,/g, '');
                });
            });

            $(document).on('input', '.expense-amount', function() {
                let value = parseNumber($(this).val());
                $(this).val(formatWithCommas(value));
                calculateTotal();
            });

            function attachExpenseRow(row) {
                const amountInput = row.querySelector('.expense-amount');
                const removeBtn = row.querySelector('.remove-expense-row');

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        row.remove();
                        calculateTotal();
                    });
                }
            }

            // 🔹 إنشاء صف مصروف جديد (بند جديد بدون معرف)
            function addExpenseRow() {
                let index = new Date().getTime(); // فهرس فريد لتجنب التعارض
                const row = document.createElement('div');
                row.className =
                    "expense-item grid grid-cols-[1fr,1.5fr,40px] gap-3 items-start p-4 bg-primary-soft rounded-lg relative";
                row.innerHTML = `
                    <input type="hidden" name="detail_ids[]" value="">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ المصروف</label>
                        <input type="text" name="expense_amount[\${index}]" placeholder="0.00"
                            class="expense-amount w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">بيان المصروف</label>
                        <input type="text" name="expense_desc[\${index}]" placeholder="مثال: صيانة مضخة"
                            class="expense-desc w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <button type="button" class="remove-expense-row text-red-600 font-bold text-lg mt-6">✖</button>
                `;
                expenseContainer.appendChild(row);
                attachExpenseRow(row);
                calculateTotal();
            }

            if (addExpenseBtn) addExpenseBtn.addEventListener('click', addExpenseRow);

            expenseContainer.querySelectorAll(':scope > .expense-item').forEach(row => {
                attachExpenseRow(row);
            });

            calculateTotal();
        });
    </script>
@endsection
