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
        // ============================================================
        // 1. Total Pendapatan Hari Ini
        //    Menjumlahkan kolom total_harga dari semua pesanan hari ini
        //    yang statusnya bukan 'Dibatalkan'.
        // ============================================================
        $totalPendapatanHariIni = Pesanan::whereDate('created_at', Carbon::today())
            ->where('status', '!=', 'Dibatalkan')
            ->sum('total_harga');

        // ============================================================
        // 2. Jumlah Transaksi Hari Ini
        //    Menghitung jumlah unik transaksi (grouped by user + timestamp).
        // ============================================================
        $jumlahTransaksi = Pesanan::whereDate('created_at', Carbon::today())
            ->select(DB::raw('DISTINCT user_id, DATE_FORMAT(created_at, "%Y-%m-%d %H:%i:%s") as trx_time'))
            ->get()
            ->count();

        // ============================================================
        // 3. Produk Terlaris
        //    Produk dengan jumlah pembelian terbanyak (all-time).
        // ============================================================
        $produkTerlaris = Pesanan::select('pesanan', DB::raw('SUM(jumlah) as total_terjual'))
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
        //    Ambil 10 pesanan terbaru beserta relasinya.
        // ============================================================
        $transaksiTerkini = Pesanan::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalPendapatanHariIni',
            'jumlahTransaksi',
            'produkTerlaris',
            'stokTipis',
            'transaksiTerkini'
        ));
    }
}
