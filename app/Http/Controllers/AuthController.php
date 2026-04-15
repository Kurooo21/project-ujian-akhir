<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // remember = true agar session tetap aktif setelah browser refresh
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], true)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil! Selamat datang, ' . Auth::user()->name,
                'csrf_token' => csrf_token(),
                'user' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'username' => Auth::user()->username,
                    'role' => Auth::user()->role,
                    'alamat' => Auth::user()->alamat,
                    'no_hp' => Auth::user()->no_hp,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau Password salah!'
        ], 401);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|string|min:4',
            'alamat' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran Berhasil! Silakan Login.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil!',
            'csrf_token' => csrf_token()
        ]);
    }

    public function user(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'logged_in' => true,
                'user' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'username' => Auth::user()->username,
                    'role' => Auth::user()->role,
                    'alamat' => Auth::user()->alamat,
                    'no_hp' => Auth::user()->no_hp,
                ]
            ]);
        }
        return response()->json(['logged_in' => false]);
    }

    /**
     * updateProfile() - Update data profil user yang sedang login
     * User bisa mengubah: nama, no_hp, alamat, dan password (opsional)
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . Auth::id(),
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'password' => 'nullable|string|min:4',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->no_hp = $request->no_hp;
        $user->alamat = $request->alamat;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'alamat' => $user->alamat,
                'no_hp' => $user->no_hp,
            ]
        ]);
    }
}
