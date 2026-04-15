<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;

class AdminPesananController extends Controller
{
    public function index()
    {
        // Ambil semua pesanan dari yang paling baru
        $pesanan = Pesanan::with('user')->orderBy('created_at', 'desc')->get();

        // Sama seperti PesananController API, kita grup berdasarkan user + waktu checkout
        $grouped = $pesanan->groupBy(function ($item) {
            return $item->user_id . '_' . $item->created_at->format('Y-m-d H:i:s');
        });

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (x' . $item->jumlah . ')';
            })->implode(', ');

            return (object) [
                'group_id' => $first->user_id . '_' . $first->created_at->format('Y-m-d H:i:s'),
                'nama_pelanggan' => $first->nama_pelanggan,
                'no_hp' => $first->no_hp,
                'alamat' => $first->alamat,
                'jenis_belanja' => $first->jenis_belanja,
                'items_summary' => $itemsList,
                'total_harga' => $items->sum('total_harga'),
                'status' => $first->status,
                'created_at' => $first->created_at,
            ];
        })->values();

        return view('admin.pesanan', compact('orders'));
    }
}
