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
                'status' => 'Sedang Disiapkan', // Status default
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

        // Kelompokkan pesanan berdasarkan user + waktu yang sama (1 checkout = 1 grup)
        $grouped = $pesanan->groupBy(function ($item) {
            // Group by user_id + timestamp (detik yang sama = 1 transaksi)
            return $item->user_id . '_' . $item->created_at->format('Y-m-d H:i:s');
        });

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            // Gabungkan semua item menjadi 1 string
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (' . $item->jumlah . 'x)';
            })->implode(', ');
            // Total harga semua item
            $totalHarga = $items->sum('total_harga');

            return [
                'id' => $first->id,
                'group_id' => $first->user_id . '|' . $first->created_at, // ID grouping untuk update massal
                'date' => $first->created_at->format('d/m/Y H:i'),
                'customerName' => $first->nama_pelanggan,
                'no_hp' => $first->no_hp,
                'alamat' => $first->alamat,
                'items' => $itemsList,
                'total' => $totalHarga,
                'jenis' => $first->jenis_belanja,
                'status' => $first->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // UPDATE STATUS PESANAN (Admin)
    public function updateStatus(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'status' => 'required|string'
        ]);

        list($userId, $createdAt) = explode('|', $request->group_id);

        Pesanan::where('user_id', $userId)
               ->where('created_at', $createdAt)
               ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    // GET PESANAN SAYA (User)
    public function userOrders()
    {
        $pesanan = Pesanan::where('user_id', Auth::id())
                          ->orderBy('created_at', 'desc')
                          ->get();

        $grouped = $pesanan->groupBy(function ($item) {
            return $item->user_id . '_' . $item->created_at->format('Y-m-d H:i:s');
        });

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (' . $item->jumlah . 'x)';
            })->implode(', ');
            $totalHarga = $items->sum('total_harga');

            return [
                'id' => $first->id,
                'date' => $first->created_at->format('d/m/Y H:i'),
                'items' => $itemsList,
                'total' => $totalHarga,
                'jenis' => $first->jenis_belanja,
                'status' => $first->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
