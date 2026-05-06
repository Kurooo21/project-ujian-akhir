{{--
    ============================================================
    HALAMAN KELOLA PESANAN KASIR
    File: resources/views/kasir/pesanan.blade.php
    ============================================================
    Halaman utama untuk kasir dalam mengelola pesanan masuk:
    - Melihat daftar pesanan untuk outlet yang ditugaskan
    - Melihat bukti pembayaran yang diunggah pelanggan
    - Mengkonfirmasi pembayaran (Lunas)
    - Mengubah status pesanan (Diproses → Selesai, dll)
    - Melihat detail lengkap pesanan via popup

    Logika penting:
    - Status pesanan TERKUNCI jika pembayaran belum dikonfirmasi
    - Dropdown status hanya muncul jika pesanan sudah Lunas

    Data dari KasirPesananController:
    - $assignedOutlet : Outlet yang ditugaskan ke kasir ini
    - $orders         : Semua pesanan untuk outlet ini (dikelompokkan per transaksi)
    ============================================================
--}}
@extends('kasir.layouts.app')
@section('page_title', 'Pesanan Outlet')

@section('content')
<!-- Peringatan jika kasir belum ditautkan ke outlet tertentu -->
@if(!$assignedOutlet)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 mb-6">
        Akun kasir ini belum ditautkan ke outlet. Hubungi admin supaya outlet kerja bisa diatur lebih dulu.
    </div>
@else
    <!-- Info Outlet -->
    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 mb-6">
        <span class="font-semibold">Outlet aktif:</span> {{ $assignedOutlet->name }}
        <span class="text-blue-700/80"> - {{ $assignedOutlet->district ?: '-' }}, {{ $assignedOutlet->city }}</span>
    </div>
@endif

{{-- TABEL PESANAN --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Header tabel: judul + deskripsi singkat --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-6 border-b border-slate-200 gap-4 bg-slate-50/30">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Kelola Pesanan Outlet</h2>
            <p class="text-sm text-slate-500 mt-1">Pantau pesanan, konfirmasi pembayaran, dan perbarui status pengiriman.</p>
        </div>
    </div>

    {{-- overflow-x-auto: tabel bisa di-scroll horizontal di layar kecil --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                {{-- Kolom-kolom header --}}
                <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
                    <th class="py-4 px-6 font-semibold">Order Info</th>        {{-- Kode + jenis pesanan --}}
                    <th class="py-4 px-6 font-semibold">Pelanggan</th>         {{-- Nama + HP + outlet --}}
                    <th class="py-4 px-6 font-semibold">Pesanan</th>           {{-- Daftar menu --}}
                    <th class="py-4 px-6 font-semibold">Total Harga</th>       {{-- Total tagihan --}}
                    <th class="py-4 px-6 font-semibold">Pembayaran</th>        {{-- Metode + status + bukti --}}
                    <th class="py-4 px-6 font-semibold">Status</th>            {{-- Dropdown status (terkunci jika belum lunas) --}}
                    <th class="py-4 px-6 font-semibold text-right">Aksi</th>   {{-- Tombol konfirmasi + detail --}}
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                {{-- @forelse: looping pesanan. Jika kosong, tampilkan pesan @empty --}}
                @forelse($orders as $index => $o)
                <tr class="hover:bg-indigo-50/30 transition-colors group">
                    
                    <!-- Order Info (ID & Tipe) -->
                    <td class="py-4 px-6 align-top">
                        <div class="flex flex-col items-start gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 border border-slate-200/60 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                {{ $o->order_code ?? 'LEGACY' }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                                @if($o->jenis_belanja === 'Take Away')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                @else
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
                                @endif
                                {{ $o->jenis_belanja }}
                            </span>
                        </div>
                    </td>
                    
                    <!-- Informasi Profil Singkat Pelanggan -->
                    <td class="py-4 px-6 align-top">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-400 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm mt-0.5">
                                {{ strtoupper(substr($o->nama_pelanggan, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 text-[13.5px] leading-tight">{{ $o->nama_pelanggan }}</span>
                                <span class="text-[11.5px] text-slate-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $o->no_hp }}
                                </span>
                                @if($o->outlet_label)
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded mt-1.5 w-fit border border-blue-100/50 flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $o->outlet_label }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    
                    <!-- Ringkasan Menu yang Dipesan -->
                    <td class="py-4 px-6 align-top">
                        <div class="text-[13px] font-medium text-slate-700 max-w-[220px] whitespace-normal leading-relaxed line-clamp-3" title="{{ $o->items_summary }}">
                            {{ $o->items_summary }}
                        </div>
                    </td>
                    
                    <!-- Total Harga Belanja -->
                    <td class="py-4 px-6 align-top">
                        <div class="font-extrabold text-slate-900 text-[14px] bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg inline-block border border-emerald-100/50">
                            Rp {{ number_format($o->total_harga, 0, ',', '.') }}
                        </div>
                    </td>
                    
                    <!-- Pembayaran (Metode, Status, Bukti) -->
                    <td class="py-4 px-6 align-top">
                        <div class="flex flex-col gap-2.5">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-md border border-slate-200/60 shadow-sm">{{ $o->payment_method_label }}</span>
                                @if(($o->payment_status ?? 'Lunas') === 'Lunas')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/50">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        LUNAS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200/50">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        MENUNGGU
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Bukti Bayar Info -->
                            <div>
                                @if($o->payment_proof_url)
                                    <button onclick="showProofImage('{{ addslashes($o->payment_proof_url) }}', '{{ addslashes($o->order_code ?? 'LEGACY') }}')" class="group flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 hover:text-blue-700 transition-colors bg-blue-50/50 px-2 py-1 rounded-md hover:bg-blue-50 w-fit">
                                        <div class="w-5 h-5 rounded bg-blue-100/50 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        Lihat Bukti
                                    </button>
                                @else
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 px-2 py-1">
                                        <div class="w-5 h-5 rounded bg-slate-50 border border-slate-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        Belum Ada
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    
                    {{-- ── KOLOM STATUS PESANAN ──────────────────────────────
                         Aturan:
                         - Jika pesanan BELUM lunas → status TERKUNCI (tampilkan badge abu)
                         - Jika pesanan SUDAH lunas → tampilkan dropdown untuk ubah status
                    ────────────────────────────────────────────────────────── --}}
                    <td class="py-4 px-6 align-top">
                        @if(($o->payment_status ?? 'Lunas') !== 'Lunas')
                            {{-- Status terkunci: pembayaran harus dikonfirmasi dulu --}}
                            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200/60 w-fit cursor-not-allowed" title="Selesaikan pembayaran terlebih dahulu">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Terkunci
                            </span>
                        @else
                            {{-- Dropdown status: kirim form PUT saat nilai berubah --}}
                            {{-- "onchange="this.form.submit()"" = auto-submit saat pilihan diubah --}}
                            <form action="{{ route('kasir.pesanan.status') }}" method="POST" class="inline-block w-full min-w-[130px] max-w-[150px]">
                                @csrf
                                @method('PUT') {{-- Laravel menerima PUT via spoofing method --}}
                                {{-- group_id digunakan server untuk update semua baris dalam satu transaksi --}}
                                <input type="hidden" name="group_id" value="{{ $o->group_id }}">
                                <div class="relative">
                                    {{-- Warna dropdown berubah dinamis sesuai status yang dipilih --}}
                                    <select name="status" onchange="this.form.submit()" class="w-full text-xs font-bold py-2 pl-3 pr-8 rounded-lg outline-none cursor-pointer appearance-none border transition-all shadow-sm focus:ring-2 focus:ring-offset-1
                                        {{ $o->status == 'Diproses' ? 'bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100 focus:ring-amber-500/20' : '' }}
                                        {{ $o->status == 'Pesanan Siap' ? 'bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100 focus:ring-blue-500/20' : '' }}
                                        {{ $o->status == 'Sedang Diantar' ? 'bg-sky-50 text-sky-700 border-sky-300 hover:bg-sky-100 focus:ring-sky-500/20' : '' }}
                                        {{ $o->status == 'Selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100 focus:ring-emerald-500/20' : '' }}
                                        {{ $o->status == 'Dibatalkan' ? 'bg-red-50 text-red-700 border-red-300 hover:bg-red-100 focus:ring-red-500/20' : '' }}
                                    ">
                                        {{-- Pilihan status sesuai alur kerja kasir --}}
                                        <option value="Diproses" {{ $o->status == 'Diproses' ? 'selected' : '' }}>Diproses</option>
                                        <option value="Pesanan Siap" {{ $o->status == 'Pesanan Siap' ? 'selected' : '' }}>Pesanan Siap</option>
                                        <option value="Sedang Diantar" {{ $o->status == 'Sedang Diantar' ? 'selected' : '' }}>Sedang Diantar</option>
                                        <option value="Selesai" {{ $o->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="Dibatalkan" {{ $o->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                    {{-- Ikon panah bawah untuk dropdown --}}
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </td>
                    
                    <!-- Aksi: Tombol Konfirmasi Pembayaran dan Detail Pesanan -->
                    <td class="py-4 px-6 align-top text-right">
                        <div class="flex flex-col items-end gap-2.5">
                            @if(($o->payment_status ?? 'Lunas') !== 'Lunas')
                                <form action="{{ route('kasir.pesanan.confirm-payment') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="group_id" value="{{ $o->group_id }}">
                                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 w-full rounded-lg text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm hover:shadow-md active:scale-95">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Terima Bayar
                                    </button>
                                </form>
                            @endif
                            <!-- Tombol Detail Pesanan -->
                            <button class="inline-flex items-center justify-center gap-1.5 px-3 py-2 w-full rounded-lg text-[11px] font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 hover:border-indigo-600 active:scale-95"
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
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail Order
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span class="text-sm font-medium">Belum ada pesanan untuk outlet ini.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- ============================================================
     @push('scripts')
     ============================================================
     Menyuntikkan script tambahan ke dalam @stack('scripts')
     yang ada di kasir/layouts/app.blade.php.

     File partials/order-detail-scripts.blade.php berisi:
     - Fungsi showOrderDetail() untuk popup detail pesanan
     - Fungsi showProofImage() untuk melihat bukti pembayaran
     ============================================================ --}}
@push('scripts')
    @include('partials.order-detail-scripts')
@endpush
