<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin.
     *
     * Halaman ini menampilkan:
     * - Total pendapatan hari ini
     * - Jumlah transaksi hari ini
     * - Produk terlaris (all-time)
     * - Jumlah produk dengan stok tipis
     * - 10 transaksi terkini
     */
    public function index()
    {
        $pesananHariIni = Pesanan::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        // ============================================================
        // 1. Total Pendapatan Hari Ini
        //    Hanya menghitung pesanan yang sudah lunas dan tidak dibatalkan.
        // ============================================================
        $totalPendapatanHariIni = $pesananHariIni
            ->where('payment_status', 'Lunas')
            ->where('status', '!=', 'Dibatalkan')
            ->sum('total_harga');

        // ============================================================
        // 2. Jumlah Transaksi Hari Ini
        //    Menghitung jumlah checkout unik berdasarkan order_code.
        // ============================================================
        $jumlahTransaksi = $pesananHariIni
            ->groupBy(fn ($item) => $this->buildGroupId($item))
            ->count();

        // ============================================================
        // 3. Produk Terlaris
        //    Produk dengan jumlah pembelian terbanyak dari pesanan lunas.
        // ============================================================
        $produkTerlaris = Pesanan::where('payment_status', 'Lunas')
            ->select('pesanan', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('pesanan')
            ->orderByDesc('total_terjual')
            ->first();

        // ============================================================
        // 4. Stok Tipis
        //    Catatan: Tabel products saat ini belum memiliki kolom 'stock'.
        //    Jika Anda sudah menambahkan kolom 'stock', uncomment baris di bawah.
        //    Untuk sementara, nilainya di-set 0.
        // ============================================================
        // $stokTipis = Product::where('stock', '<=', 10)->count();
        $stokTipis = 0;

        // ============================================================
        // 5. Transaksi Terkini
        //    Ambil 10 checkout terbaru dan gabungkan itemnya.
        // ============================================================
        $transaksiTerkini = Pesanan::orderBy('created_at', 'desc')
            ->get();

        $transaksiTerkini = $transaksiTerkini
            ->groupBy(fn ($item) => $this->buildGroupId($item))
            ->map(function ($items) {
                $first = $items->first();

                return (object) [
                    'id' => $first->id,
                    'order_code' => $first->order_code,
                    'nama_pelanggan' => $first->nama_pelanggan,
                    'no_hp' => $first->no_hp,
                    'pesanan' => $items->map(fn ($item) => $item->pesanan . ' (x' . $item->jumlah . ')')->implode(', '),
                    'total_harga' => $items->sum('total_harga'),
                    'jenis_belanja' => $first->jenis_belanja,
                    'payment_status' => $first->payment_status ?? 'Lunas',
                    'status' => $first->status,
                    'created_at' => $first->created_at,
                ];
            })
            ->take(10)
            ->values();

        return view('admin.dashboard', compact(
            'totalPendapatanHariIni',
            'jumlahTransaksi',
            'produkTerlaris',
            'stokTipis',
            'transaksiTerkini'
        ));
    }

    private function buildGroupId(Pesanan $pesanan): string
    {
        if ($pesanan->order_code) {
            return $pesanan->order_code;
        }

        return $pesanan->user_id . '|' . $pesanan->created_at->format('Y-m-d H:i:s');
    }
}
