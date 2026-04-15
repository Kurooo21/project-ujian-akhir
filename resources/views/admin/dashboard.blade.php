@extends('admin.layouts.app')
@section('page_title', 'Dashboard')

@section('content')
<!-- RINGKASAN — Grid 4 Kolom -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">

    <!-- 1. Total Pendapatan Hari Ini -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 relative overflow-hidden before:absolute before:top-0 before:left-0 before:w-full before:h-1 before:bg-gradient-to-r before:from-blue-600 before:to-blue-400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-green-50 text-green-600">▲ 12%</span>
        </div>
        <div class="text-[28px] font-extrabold text-slate-900 tracking-tight mb-1">Rp {{ number_format($totalPendapatanHariIni ?? 0, 0, ',', '.') }}</div>
        <div class="text-[13px] font-medium text-slate-400">Total Pendapatan Hari Ini</div>
    </div>

    <!-- 2. Jumlah Transaksi -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 relative overflow-hidden before:absolute before:top-0 before:left-0 before:w-full before:h-1 before:bg-gradient-to-r before:from-green-600 before:to-green-400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-600">Hari Ini</span>
        </div>
        <div class="text-[28px] font-extrabold text-slate-900 tracking-tight mb-1">{{ $jumlahTransaksi ?? 0 }}</div>
        <div class="text-[13px] font-medium text-slate-400">Jumlah Transaksi</div>
    </div>

    <!-- 3. Produk Terlaris -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 relative overflow-hidden before:absolute before:top-0 before:left-0 before:w-full before:h-1 before:bg-gradient-to-r before:from-amber-500 before:to-yellow-400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600">Top #1</span>
        </div>
        <div class="text-xl font-extrabold text-slate-900 tracking-tight mb-1 truncate">{{ $produkTerlaris->pesanan ?? '—' }}</div>
        <div class="text-[13px] font-medium text-slate-400">Produk Terlaris</div>
    </div>

    <!-- 4. Stok Tipis -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 relative overflow-hidden before:absolute before:top-0 before:left-0 before:w-full before:h-1 before:bg-gradient-to-r before:from-red-600 before:to-red-400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-600">Peringatan</span>
        </div>
        <div class="text-[28px] font-extrabold text-slate-900 tracking-tight mb-1">{{ $stokTipis ?? 0 }}</div>
        <div class="text-[13px] font-medium text-slate-400">Produk Stok Tipis</div>
    </div>

</div>

<!-- TABEL TRANSAKSI TERKINI -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Transaksi Terkini</h2>
            <span class="text-xs text-slate-400">Daftar pesanan terbaru yang masuk</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 border border-slate-200 rounded-md text-[13px] font-medium text-slate-600 bg-white hover:border-blue-600 hover:text-blue-600 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filter
            </button>
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 border border-slate-200 rounded-md text-[13px] font-medium text-slate-600 bg-white hover:border-blue-600 hover:text-blue-600 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">No</th>
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Pesanan</th>
                    <th class="py-3 px-6">Total</th>
                    <th class="py-3 px-6">Jenis</th>
                    <th class="py-3 px-6">Status</th>
                    <th class="py-3 px-6">Tanggal</th>
                    <th class="py-3 px-6">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($transaksiTerkini as $index => $trx)
                <tr class="hover:bg-blue-50/50 transition-colors">
                    <td class="py-3.5 px-6">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($trx->nama_pelanggan, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900">{{ $trx->nama_pelanggan }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trx->no_hp }}</div>
                                @if(!empty($trx->order_code))
                                    <div class="text-[11px] font-bold text-red-600 mt-0.5">{{ $trx->order_code }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6 font-medium">{{ $trx->pesanan }}</td>
                    <td class="py-3.5 px-6 font-semibold text-slate-900">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-6">{{ $trx->jenis_belanja }}</td>
                    <td class="py-3.5 px-6">
                        @if($trx->status == 'Menunggu Pembayaran')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @elseif($trx->status == 'Diproses' || $trx->status == 'Sedang Disiapkan')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-600 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @elseif($trx->status == 'Pesanan Siap')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @elseif($trx->status == 'Selesai')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-600 border border-green-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @elseif($trx->status == 'Dikirim' || $trx->status == 'Sedang Diantar')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 border border-red-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $trx->status }}
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3.5 px-6">
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition-colors" onclick="alert('Order ID: {{ $trx->order_code ?? 'LEGACY' }}\nPembayaran: {{ $trx->payment_status ?? 'Lunas' }}\nStatus: {{ $trx->status }}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada transaksi hari ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Padding Kosong di bawah karena pagination belum diimplementasi -->
    <div class="px-6 py-4 border-t border-slate-200">
        <span class="text-xs text-slate-400">Menampilkan 10 transaksi terakhir</span>
    </div>
</div>
@endsection
