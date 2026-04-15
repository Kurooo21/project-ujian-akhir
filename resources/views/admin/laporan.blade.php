@extends('admin.layouts.app')
@section('page_title', 'Laporan Pendapatan')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
    <!-- Pemasukan All-time -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Total Pemasukan (All-time)</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight">Rp {{ number_format($totalPemasukan ?? 0, 0, ',', '.') }}</div>
    </div>

    <!-- Pemasukan Bulan Ini -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Pemasukan Bulan Ini</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-green-600">Rp {{ number_format($pemasukanBulanIni ?? 0, 0, ',', '.') }}</div>
    </div>

    <!-- Makan Ditempat -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Makan Ditempat</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-blue-600">Rp {{ number_format($pemasukanMakanDitempat ?? 0, 0, ',', '.') }}</div>
    </div>

    <!-- Bawa Pulang -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[13px] font-medium text-slate-400 mb-1">Bawa Pulang</div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight text-amber-600">Rp {{ number_format($pemasukanBawaPulang ?? 0, 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Histori Transaksi Sukses</h2>
            <span class="text-xs text-slate-400">Daftar pesanan dengan status 'Selesai'</span>
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
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Pesanan</th>
                    <th class="py-3 px-6 text-right">Pendapatan</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($pesananSukses as $pesanan)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3.5 px-6">{{ $pesanan->created_at->format('d M Y, H:i') }}</td>
                    <td class="py-3.5 px-6 font-semibold">{{ $pesanan->nama_pelanggan }}</td>
                    <td class="py-3.5 px-6">{{ $pesanan->pesanan }} (x{{ $pesanan->jumlah }})</td>
                    <td class="py-3.5 px-6 text-right font-semibold text-green-600">+ Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-10 text-slate-400">Belum ada transaksi sukses yang dapat direkap.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
