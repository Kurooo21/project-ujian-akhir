<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminPelangganController extends Controller
{
    public function index()
    {
        $pelanggan = User::where('role', 'pelanggan')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pelanggan', compact('pelanggan'));
    }
}
