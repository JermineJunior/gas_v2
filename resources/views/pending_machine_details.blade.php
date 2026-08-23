@extends('layouts.app')

@section('title', 'قراءات بانتظار الموافقة')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('content')
    <div class="max-w-7xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-6">
        <!-- توكن للنماذج المنشأة برمجياً -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">قراءات بانتظار الموافقة</h2>
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold">{{ $pending->count() }}</span>
        </div>

        @if (session('success'))
        @endif

        <div class="overflow-x-auto">            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead class="bg-primary-strong text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">المحطة</th>
                        <th class="px-4 py-3 text-right">الموظف</th>
                        <th class="px-4 py-3 text-right">الماكينة</th>
                        <th class="px-4 py-3 text-right">المسدس</th>
                        <th class="px-4 py-3 text-right">عداد البداية</th>
                        <th class="px-4 py-3 text-right">عداد النهاية</th>
                        <th class="px-4 py-3 text-right">كمية التصفير</th>
                        <th class="px-4 py-3 text-center">الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pending as $detail)
                        <tr class="border-b hover:bg-gray-50 {{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $detail->date->format('Y/m/d') }}</td>
                            <td class="px-4 py-3">{{ $detail->station->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $detail->employee->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $detail->machine->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $detail->gun->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ number_format($detail->start_counter) }}</td>
                            <td class="px-4 py-3">{{ number_format($detail->end_counter) }}</td>
                            <td class="px-4 py-3 font-bold text-amber-700">{{ number_format($detail->net) }} لتر</td>
                            <td class="px-4 py-3 flex gap-2 flex-wrap">
                                <button type="button"
                                    class="approve-row-btn bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700"
                                    data-url="{{ route('machine_details.approve', $detail->id) }}">اعتماد وخصم</button>
                                <button type="button"
                                    class="reject-row-btn bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700"
                                    data-url="{{ route('machine_details.reject', $detail->id) }}">رفض</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                لا توجد قراءات بانتظار الموافقة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
    <script>
        function submitAction(url, confirmTitle, confirmText, confirmColor, confirmText2) {
            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#d33',
                confirmButtonText: confirmText2,
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    const f = document.createElement('form');
                    f.method = 'POST';
                    f.action = url;
                    const token = document.querySelector('input[name="_token"]');
                    if (token) f.appendChild(token.cloneNode());
                    document.body.appendChild(f);
                    f.submit();
                }
            });
        }

        $(document).ready(function() {
            document.querySelectorAll('.approve-row-btn').forEach(button => {
                button.addEventListener('click', function() {
                    submitAction(this.dataset.url, 'اعتماد القراءة؟',
                        'سيتم خصم كمية التصفير من البير ولا يمكن التراجع',
                        '#3085d6', 'نعم، اعتماد وخصم');
                });
            });

            document.querySelectorAll('.reject-row-btn').forEach(button => {
                button.addEventListener('click', function() {
                    submitAction(this.dataset.url, 'رفض القراءة؟',
                        'لن يتم خصم أي كمية من البير',
                        '#d33', 'نعم، رفض');
                });
            });

            // قائمة الموبايل (الهامبرجر)
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
                window.addEventListener('click', function(e) {
                    if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endsection
