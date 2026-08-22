@extends('layouts.app')

@section('title', 'تعديل المستودع')

@section('body-class', 'bg-gray-100 min-h-screen p-6')

@section('styles')
@endsection

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">تعديل المستودع: {{ $warehouse->name }}</h2>
            <a href="{{ route('warehouses.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">رجوع</a>
        </div>

        <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">اسم المستودع</label>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-primary-strong text-white px-6 py-2 rounded-lg hover:bg-primary-strong transition">
                    تحديث
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
