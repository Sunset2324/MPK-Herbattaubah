<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', ['users' => User::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
        ], ['email.unique' => 'Email sudah terdaftar.']);
        User::create($data + ['role' => 'admin']);

        return redirect()->route('admin.users')->with('ok', 'Admin baru ditambahkan.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current' => ['required', 'current_password'],
            'new' => ['required', Password::min(8)],
        ], ['current.current_password' => 'Password lama salah.'], ['new' => 'password baru']);
        $request->user()->update(['password' => $request->input('new')]);

        return redirect()->route('admin.users')->with('ok', 'Password diganti.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.users')->with('err', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();

        return redirect()->route('admin.users')->with('ok', 'Akun admin dihapus.');
    }
}
