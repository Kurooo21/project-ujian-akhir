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

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil! Selamat datang, ' . Auth::user()->name,
                'user' => [
                    'name' => Auth::user()->name,
                    'username' => Auth::user()->username,
                    'role' => Auth::user()->role,
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
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:4',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
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
            'message' => 'Logout berhasil!'
        ]);
    }

    public function user(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'logged_in' => true,
                'user' => [
                    'name' => Auth::user()->name,
                    'username' => Auth::user()->username,
                    'role' => Auth::user()->role,
                ]
            ]);
        }
        return response()->json(['logged_in' => false]);
    }
}
