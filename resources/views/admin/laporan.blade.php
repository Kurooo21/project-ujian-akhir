{{--
    ============================================================
    HALAMAN LAPORAN PENDAPATAN
    File: resources/views/admin/laporan.blade.php
    ============================================================
    Halaman ini menampilkan rekapitulasi pendapatan toko secara
    menyeluruh, hanya dari pesanan dengan status 'Selesai'.

    Data dari AdminLaporanController:
    - $totalPemasukan           : Total pendapatan dari semua waktu
    - $pemasukanBulanIni        : Pendapatan bulan berjalan
    - $pemasukanAmbilDiOutlet   : Pendapatan dari jenis "Take Away"
    - $pemasukanDelivery        : Pendapatan dari jenis "Delivery"
    - $laporanPerOutlet         : Koleksi ringkasan per outlet
    - $hasMultipleOutlets       : Boolean, apakah ada lebih dari 1 outlet
    - $pesananSukses            : Daftar semua pesanan yang sudah 'Selesai'
    ============================================================
--}}
@extends('admin.layouts.app')
@section('page_title', 'Laporan Pendapatan')

@section('content')

{{-- ============================================================
     KARTU STATISTIK RINGKASAN (4 Kolom)
     ============================================================
     4 angka utama dalam kartu yang mudah dibaca sekilas
     ============================================================ --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
    {{-- Kartu 1: Total pendapatan dari awal hingga sekarang --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Total Pemasukan (All-time)</div>
        {{-- "?? 0" = jika variabel null, gunakan 0 sebagai fallback --}}
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight">Rp {{ number_format($totalPemasukan ?? 0, 0, ',', '.') }}</div>
    </div>

    {{-- Kartu 2: Pendapatan bulan berjalan saja --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Pemasukan Bulan Ini</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-green-600">Rp {{ number_format($pemasukanBulanIni ?? 0, 0, ',', '.') }}</div>
    </div>

    {{-- Kartu 3: Pendapatan dari pesanan Take Away / Ambil di Outlet --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Ambil di Outlet</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-blue-600">Rp {{ number_format($pemasukanAmbilDiOutlet ?? 0, 0, ',', '.') }}</div>
    </div>

    {{-- Kartu 4: Pendapatan dari pesanan Delivery --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Delivery</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-amber-600">Rp {{ number_format($pemasukanDelivery ?? 0, 0, ',', '.') }}</div>
    </div>
</div>

{{-- ============================================================
     RINGKASAN PER OUTLET (hanya tampil jika ada data outlet)
     ============================================================
     Bagian ini menampilkan kartu ringkasan untuk setiap outlet:
     - Total pendapatan outlet
     - Pendapatan bulan ini
     - Breakdown: Ambil di outlet vs Delivery

     Blok ini disembunyikan jika $laporanPerOutlet kosong
     ============================================================ --}}
{{-- collect() = buat Collection kosong sebagai fallback jika variabel null --}}
@if(($laporanPerOutlet ?? collect())->isNotEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-7">
    <div class="p-5 lg:px-6 border-b border-slate-200">
        <h2 class="text-base font-bold text-slate-900 tracking-tight">Ringkasan Penjualan per Outlet</h2>
        <span class="text-xs text-slate-400">
            @if($hasMultipleOutlets ?? false)
                Laporan dibedakan per outlet karena data transaksi berasal dari lebih dari satu outlet.
            @else
                Saat ini seluruh transaksi selesai tercatat pada satu outlet aktif.
            @endif
        </span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 p-5 lg:p-6">
        @foreach($laporanPerOutlet as $laporanOutlet)
        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Outlet</div>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $laporanOutlet->outlet_label }}</h3>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $laporanOutlet->jumlah_transaksi }} transaksi selesai
                        @if($laporanOutlet->pesanan_terakhir)
                            • Update terakhir {{ $laporanOutlet->pesanan_terakhir->format('d M Y, H:i') }}
                        @endif
                    </p>
                </div>
                <div class="rounded-2xl bg-white px-4 py-3 border border-slate-200 min-w-[180px]">
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Total Outlet</div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-900">Rp {{ number_format($laporanOutlet->total_pemasukan ?? 0, 0, ',', '.') }}</div>
                    <div class="mt-1 text-xs text-green-600 font-semibold">Bulan ini Rp {{ number_format($laporanOutlet->pemasukan_bulan_ini ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                <div class="rounded-xl bg-white border border-slate-200 px-4 py-3">
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Ambil di Outlet</div>
                    <div class="mt-2 text-lg font-extrabold text-blue-600">Rp {{ number_format($laporanOutlet->pemasukan_ambil_di_outlet ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 px-4 py-3">
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Delivery</div>
                    <div class="mt-2 text-lg font-extrabold text-amber-600">Rp {{ number_format($laporanOutlet->pemasukan_delivery ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Histori Transaksi Sukses</h2>
            <span class="text-xs text-slate-400">Daftar pesanan dengan status 'Selesai' @if($hasMultipleOutlets ?? false) dari seluruh outlet @endif</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 border border-slate-200 rounded-md text-[13px] font-medium text-slate-600 bg-white hover:border-blue-600 hover:text-blue-600 transition-colors" onclick="alert('Mencetak Laporan PDF')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Cetak PDF
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">Tanggal</th>
                    <th class="py-3 px-6">Outlet</th>
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Pesanan</th>
                    <th class="py-3 px-6">Jenis</th>
                    <th class="py-3 px-6 text-right">Pendapatan</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($pesananSukses as $pesanan)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3.5 px-6">{{ $pesanan->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-3.5 px-6">
                        <div class="font-semibold text-slate-900">{{ $pesanan->outlet_name ?: 'Tanpa Outlet' }}</div>
                        @if($pesanan->outlet_district || $pesanan->outlet_city)
                            <div class="text-[11px] text-slate-400">{{ collect([$pesanan->outlet_district, $pesanan->outlet_city])->filter()->implode(', ') }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 font-semibold">{{ $pesanan->nama_pelanggan }}</td>
                    <td class="py-3.5 px-6">{{ $pesanan->pesanan }} (x{{ $pesanan->jumlah }})</td>
                    <td class="py-3.5 px-6">{{ $pesanan->jenis_belanja ?: '-' }}</td>
                    <td class="py-3.5 px-6 text-right font-semibold text-green-600">+ Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-slate-400">Belum ada transaksi sukses yang dapat direkap.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
