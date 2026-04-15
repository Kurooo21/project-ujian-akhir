<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminLaporanController extends Controller
{
    public function index()
    {
        // Total Pemasukan All-time (Hanya dari pesanan 'Selesai')
        $totalPemasukan = Pesanan::where('status', 'Selesai')->sum('total_harga');
        
        // Pemasukan Bulan Ini
        $pemasukanBulanIni = Pesanan::where('status', 'Selesai')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_harga');

        // Pemasukan berdasarkan metode ('Makan Ditempat', 'Takeaway')
        $pemasukanMakanDitempat = Pesanan::where('status', 'Selesai')
            ->where('jenis_belanja', 'makan-ditempat')->sum('total_harga');
            
        $pemasukanBawaPulang = Pesanan::where('status', 'Selesai')
            ->where('jenis_belanja', 'bawa-pulang')->sum('total_harga');

        // Pesanan terbaru yang sukses
        $pesananSukses = Pesanan::where('status', 'Selesai')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.laporan', compact(
            'totalPemasukan', 
            'pemasukanBulanIni', 
            'pemasukanMakanDitempat', 
            'pemasukanBawaPulang',
            'pesananSukses'
        ));
    }
}
