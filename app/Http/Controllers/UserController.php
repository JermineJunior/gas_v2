<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::whereNot('id', 1)->get();
        $stations = Station::get();
        return view('user', compact('users', 'stations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'station_id' => ['required', 'exists:stations,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        $user->stations()->syncWithoutDetaching($request->station_id);

        return back()->with('success', 'تم اضافة المستخدم بنجاج');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => ['required', Rule::unique('users', 'username')->ignore($request->id, 'id')],
            'password' => 'nullable',
            'station_id' => 'required|exists:stations,id',
        ]);

        $user = User::findOrFail($request->id);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
        ]);

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->stations()->sync($request->station_id);

        return back()->with('success', 'تم تعديل المستخدم بنجاج');
    }

    public function delete(User $user)
    {
        $user->delete();
        return back()->with('success', 'تم حذف المستخدم بنجاح');
    }

    public function reset(User $user)
    {
        $user->update([
            'password' => bcrypt('123'),
        ]);
        return back()->with('success', 'تم إستغادة كلمة السر بنجاح');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'كلمة السر القديمة غير صحيحة.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', 'تم تحديث كلمة السر بنجاح.');
    }
}
