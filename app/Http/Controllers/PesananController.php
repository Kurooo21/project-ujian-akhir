<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'pesanan_item' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric',
            'jenis_belanja' => 'required|string|max:50',
        ]);

        $total_harga = $request->jumlah * $request->harga_satuan;

        Pesanan::create([
            'user_id' => Auth::id(),
            'nama_pelanggan' => $request->nama,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'pesanan' => $request->pesanan_item,
            'jumlah' => $request->jumlah,
            'harga_satuan' => $request->harga_satuan,
            'total_harga' => $total_harga,
            'jenis_belanja' => $request->jenis_belanja,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat! Terima kasih.'
        ]);
    }

    public function index()
    {
        $pesanan = Pesanan::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $pesanan->map(function ($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'customerName' => $order->nama_pelanggan,
                    'items' => $order->pesanan . ' (' . $order->jumlah . 'x)',
                    'total' => $order->total_harga,
                    'jenis' => $order->jenis_belanja,
                ];
            })
        ]);
    }
}
