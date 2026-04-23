@extends('kasir.layouts.app')
@section('page_title', 'Dashboard Kasir')

@section('content')
@if(!$assignedOutlet)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 mb-6">
        Akun kasir ini belum ditautkan ke outlet. Selama outlet belum diatur, data pesanan tidak bisa ditampilkan.
    </div>
@else
    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 mb-6">
        <span class="font-semibold">Outlet yang kamu tangani:</span> {{ $assignedOutlet->name }}
        <span class="text-blue-700/80"> - {{ $assignedOutlet->district ?: '-' }}, {{ $assignedOutlet->city }}</span>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[12px] uppercase tracking-widest text-slate-400 font-semibold">Pendapatan Hari Ini</div>
        <div class="mt-2 text-3xl font-extrabold text-slate-900">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
        <div class="mt-1 text-sm text-slate-500">Hanya order lunas yang tidak dibatalkan</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[12px] uppercase tracking-widest text-slate-400 font-semibold">Transaksi Hari Ini</div>
        <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $todayOrderCount }}</div>
        <div class="mt-1 text-sm text-slate-500">Order yang masuk untuk outlet ini</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-amber-200 bg-amber-50/50">
        <div class="text-[12px] uppercase tracking-widest text-amber-600 font-semibold">Menunggu Pembayaran</div>
        <div class="mt-2 text-3xl font-extrabold text-amber-700">{{ $pendingPaymentCount }}</div>
        <div class="mt-1 text-sm text-amber-700/80">Perlu verifikasi kasir</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-200 bg-emerald-50/50">
        <div class="text-[12px] uppercase tracking-widest text-emerald-600 font-semibold">Selesai Hari Ini</div>
        <div class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $completedTodayCount }}</div>
        <div class="mt-1 text-sm text-emerald-700/80">{{ $activeOrderCount }} pesanan masih berjalan</div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Pesanan Terbaru</h2>
            <span class="text-xs text-slate-400">Ringkasan cepat sebelum lanjut ke halaman kelola pesanan.</span>
        </div>
        <a href="{{ route('kasir.pesanan') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-[13px] font-semibold transition-colors">
            Buka Kelola Pesanan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">Order ID</th>
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Pesanan</th>
                    <th class="py-3 px-6">Pembayaran</th>
                    <th class="py-3 px-6">Status</th>
                    <th class="py-3 px-6">Waktu</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $order->order_code ?? 'LEGACY' }}</td>
                    <td class="py-3.5 px-6">
                        <div class="font-semibold text-slate-900">{{ $order->nama_pelanggan }}</div>
                        <div class="text-[11px] text-slate-400">{{ $order->no_hp }}</div>
                    </td>
                    <td class="py-3.5 px-6 max-w-[240px] truncate" title="{{ $order->items_summary }}">{{ $order->items_summary }}</td>
                    <td class="py-3.5 px-6">
                        @if(($order->payment_status ?? 'Lunas') === 'Lunas')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-600 border border-green-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Lunas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $order->payment_status }}
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">{{ $order->status }}</td>
                    <td class="py-3.5 px-6 text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-slate-400">Belum ada pesanan yang masuk untuk outlet ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
