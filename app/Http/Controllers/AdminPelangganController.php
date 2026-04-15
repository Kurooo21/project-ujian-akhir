<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminPelangganController extends Controller
{
    public function index()
    {
        // Asumsi pelanggan adalah user yang role-nya != 'admin'
        $pelanggan = User::where('role', '!=', 'admin')->orderBy('created_at', 'desc')->get();
        return view('admin.pelanggan', compact('pelanggan'));
    }
}
