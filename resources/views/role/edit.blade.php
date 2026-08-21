@extends('layouts.app')

@section('title', 'تعديل الدور')

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">تعديل الدور: {{ $role->name }}</h2>
            <a href="{{ route('roles.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                رجوع
            </a>
        </div>

        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">اسم الدور</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">الصلاحيات</label>
                @error('permissions')
                    <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                @enderror
                @foreach($permissions as $group => $perms)
                    @php
                        $allChecked = true;
                        foreach ($perms as $key => $label) {
                            if (!$role->hasPermissionTo($key)) {
                                $allChecked = false;
                                break;
                            }
                        }
                    @endphp
                    <div class="mb-4 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <input type="checkbox" id="group_{{ $group }}" class="group-check rounded"
                                {{ $allChecked ? 'checked' : '' }}
                                onchange="toggleGroup('{{ $group }}')">
                            <label for="group_{{ $group }}" class="font-semibold text-gray-700">{{ $groupLabels[$group] ?? $group }}</label>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mr-6">
                            @foreach($perms as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                        class="perm-{{ $group }} rounded"
                                        {{ $role->hasPermissionTo($key) ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    تحديث
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleGroup(group) {
            const groupCheck = document.getElementById('group_' + group);
            document.querySelectorAll('.perm-' + group).forEach(cb => {
                cb.checked = groupCheck.checked;
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('messages')
@endsection
