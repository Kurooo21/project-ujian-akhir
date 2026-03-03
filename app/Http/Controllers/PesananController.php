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
            'jenis_belanja' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.pesanan_item' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric',
        ]);

        foreach ($request->items as $item) {
            $total_harga = $item['jumlah'] * $item['harga_satuan'];

            Pesanan::create([
                'user_id' => Auth::id(),
                'nama_pelanggan' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'pesanan' => $item['pesanan_item'],
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $item['harga_satuan'],
                'total_harga' => $total_harga,
                'jenis_belanja' => $request->jenis_belanja,
            ]);
        }

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
