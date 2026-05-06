{{--
    ============================================================
    HALAMAN MONITORING PESANAN (Admin)
    File: resources/views/admin/pesanan.blade.php
    ============================================================
    Admin HANYA bisa memantau (monitor) pesanan dari semua outlet.
    Admin TIDAK bisa mengubah status pesanan atau konfirmasi bayar.
    Semua aksi tersebut hanya bisa dilakukan dari panel Kasir.

    Data dari AdminPesananController:
    - $orders              : Semua pesanan dari seluruh outlet (dikelompokkan)
    - $pendingPaymentCount : Jumlah pesanan yang menunggu konfirmasi bayar
    - $completedOrderCount : Jumlah pesanan yang sudah selesai
    - $activeOrderCount    : Jumlah pesanan yang masih aktif
    ============================================================
--}}
@extends('admin.layouts.app')
@section('page_title', 'Monitoring Pesanan')

@section('content')

{{-- STATISTIK RINGKASAN: 3 kartu angka pesanan --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    {{-- Kartu 1: Total seluruh pesanan dari semua outlet --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[12px] uppercase tracking-widest text-slate-400 font-semibold">Total Order</div>
        {{-- $orders->count() = hitung jumlah item dalam koleksi pesanan --}}
        <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $orders->count() }}</div>
        <div class="mt-1 text-sm text-slate-500">Semua pesanan dari seluruh outlet</div>
    </div>

    {{-- Kartu 2: Pesanan belum lunas — perlu tindakan kasir (warna kuning) --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-200 bg-amber-50/60">
        <div class="text-[12px] uppercase tracking-widest text-amber-600 font-semibold">Menunggu Bayar</div>
        <div class="mt-2 text-3xl font-extrabold text-amber-700">{{ $pendingPaymentCount }}</div>
        <div class="mt-1 text-sm text-amber-700/80">Perlu dicek oleh kasir outlet terkait</div>
    </div>

    {{-- Kartu 3: Pesanan sudah selesai (warna hijau = positif) --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-200 bg-emerald-50/60">
        <div class="text-[12px] uppercase tracking-widest text-emerald-600 font-semibold">Selesai</div>
        <div class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $completedOrderCount }}</div>
        <div class="mt-1 text-sm text-emerald-700/80">Order yang sudah dituntaskan</div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Pantauan Pesanan Seluruh Outlet</h2>
            <span class="text-xs text-slate-400">Admin hanya memonitor. Konfirmasi pembayaran dan perubahan status dikerjakan dari panel kasir.</span>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            {{ $activeOrderCount }} pesanan masih berjalan
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">No</th>
                    <th class="py-3 px-6">Order ID</th>
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Outlet</th>
                    <th class="py-3 px-6">Detail Pesanan</th>
                    <th class="py-3 px-6">Pembayaran</th>
                    <th class="py-3 px-6">Status</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($orders as $index => $o)
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="py-3.5 px-6">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            {{ $o->order_code ?? 'LEGACY' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="font-semibold text-slate-900">{{ $o->nama_pelanggan }}</div>
                        <div class="text-[11px] text-slate-400">{{ $o->no_hp }}</div>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="font-semibold text-slate-900">{{ $o->outlet_label ?: '-' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $o->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="py-3.5 px-6 max-w-[220px] truncate" title="{{ $o->items_summary }}">{{ $o->items_summary }}</td>
                    <td class="py-3.5 px-6">
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-semibold text-slate-900">{{ $o->payment_method_label }}</span>
                            @if(($o->payment_status ?? 'Lunas') === 'Lunas')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-600 border border-green-100 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Lunas
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-100 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $o->payment_status }}
                                </span>
                            @endif
                        </div>
                    </td>
                    {{-- Kolom Status Pesanan: Warna badge dinamis berdasarkan nilai $o->status --}}
                    <td class="py-3.5 px-6">
                        @php
                            // match() = switch modern PHP 8+: cocokkan nilai $o->status ke class warna
                            $statusClasses = match ($o->status) {
                                'Diproses'      => 'bg-amber-50 text-amber-700 border-amber-200', // Kuning
                                'Pesanan Siap'  => 'bg-blue-50 text-blue-600 border-blue-200',   // Biru
                                'Sedang Diantar'=> 'bg-sky-50 text-sky-600 border-sky-200',      // Biru muda
                                'Selesai'       => 'bg-green-50 text-green-600 border-green-200',// Hijau
                                'Dibatalkan'    => 'bg-red-50 text-red-600 border-red-200',      // Merah
                                default         => 'bg-slate-100 text-slate-600 border-slate-200',// Abu (fallback)
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusClasses }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $o->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition-colors"
                            onclick="showOrderDetail({
                                order_code: '{{ addslashes($o->order_code ?? 'LEGACY') }}',
                                outlet_label: '{{ addslashes($o->outlet_label ?? '-') }}',
                                payment_method_label: '{{ addslashes($o->payment_method_label) }}',
                                payment_status: '{{ addslashes($o->payment_status ?? 'Lunas') }}',
                                alamat: '{{ addslashes($o->alamat) }}',
                                outlet_address: '{{ addslashes($o->outlet_address ?? '-') }}',
                                waktu: '{{ $o->created_at->format('d/m/Y H:i') }}',
                                nama_pelanggan: '{{ addslashes($o->nama_pelanggan) }}',
                                no_hp: '{{ addslashes($o->no_hp) }}',
                                jenis_belanja: '{{ addslashes($o->jenis_belanja) }}',
                                total_harga: '{{ number_format($o->total_harga, 0, ',', '.') }}',
                                payment_proof_url: '{{ addslashes($o->payment_proof_url ?? '') }}',
                                payment_proof_uploaded_at: '{{ $o->payment_proof_uploaded_at ? $o->payment_proof_uploaded_at->format('d/m/Y H:i') : '' }}'
                            })">
                            Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada pesanan yang tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    @include('partials.order-detail-scripts')
@endpush
