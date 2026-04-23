@extends('kasir.layouts.app')
@section('page_title', 'Pesanan Outlet')

@section('content')
@if(!$assignedOutlet)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 mb-6">
        Akun kasir ini belum ditautkan ke outlet. Hubungi admin supaya outlet kerja bisa diatur lebih dulu.
    </div>
@else
    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 mb-6">
        <span class="font-semibold">Outlet aktif:</span> {{ $assignedOutlet->name }}
        <span class="text-blue-700/80"> - {{ $assignedOutlet->district ?: '-' }}, {{ $assignedOutlet->city }}</span>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Kelola Pesanan Outlet</h2>
            <span class="text-xs text-slate-400">Kasir bertugas mengonfirmasi pembayaran dan menggerakkan status pesanan sampai selesai.</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">No</th>
                    <th class="py-3 px-6">Order ID</th>
                    <th class="py-3 px-6">Pelanggan</th>
                    <th class="py-3 px-6">Detail Pesanan</th>
                    <th class="py-3 px-6">Total Harga</th>
                    <th class="py-3 px-6">Tipe</th>
                    <th class="py-3 px-6">Pembayaran</th>
                    <th class="py-3 px-6">Bukti Bayar</th>
                    <th class="py-3 px-6">Status Pesanan</th>
                    <th class="py-3 px-6">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($orders as $index => $o)
                <tr class="hover:bg-blue-50/50 transition-colors">
                    <td class="py-3.5 px-6">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            {{ $o->order_code ?? 'LEGACY' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($o->nama_pelanggan, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900">{{ $o->nama_pelanggan }}</div>
                                <div class="text-[11px] text-slate-400">{{ $o->no_hp }}</div>
                                @if($o->outlet_label)
                                    <div class="text-[11px] font-semibold text-blue-600 mt-0.5">{{ $o->outlet_label }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6 font-medium max-w-[220px] truncate" title="{{ $o->items_summary }}">{{ $o->items_summary }}</td>
                    <td class="py-3.5 px-6 font-semibold text-slate-900">Rp {{ number_format($o->total_harga, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-6">{{ $o->jenis_belanja }}</td>
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
                    <td class="py-3.5 px-6">
                        @if($o->payment_proof_url)
                            <button
                                onclick="showProofImage('{{ addslashes($o->payment_proof_url) }}', '{{ addslashes($o->order_code ?? 'LEGACY') }}')"
                                class="group block w-14 h-14 rounded-xl overflow-hidden border-2 border-green-200 hover:border-green-400 transition-all shadow-sm hover:shadow-md relative"
                                title="Lihat bukti pembayaran"
                            >
                                <img src="{{ $o->payment_proof_url }}" alt="Bukti" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </div>
                            </button>
                            <span class="block mt-1 text-[10px] text-green-600 font-semibold">Sudah Upload</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-400 border border-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Belum Upload
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">
                        @if(($o->payment_status ?? 'Lunas') !== 'Lunas')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Menunggu Konfirmasi
                            </span>
                        @else
                            <form action="{{ route('kasir.pesanan.status') }}" method="POST" class="inline-block">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="group_id" value="{{ $o->group_id }}">
                                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold py-1.5 px-2.5 rounded-full outline-none cursor-pointer border
                                    {{ $o->status == 'Diproses' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                    {{ $o->status == 'Pesanan Siap' ? 'bg-blue-50 text-blue-600 border-blue-200' : '' }}
                                    {{ $o->status == 'Sedang Diantar' ? 'bg-sky-50 text-sky-600 border-sky-200' : '' }}
                                    {{ $o->status == 'Selesai' ? 'bg-green-50 text-green-600 border-green-200' : '' }}
                                    {{ $o->status == 'Dibatalkan' ? 'bg-red-50 text-red-600 border-red-200' : '' }}
                                ">
                                    <option value="Diproses" {{ $o->status == 'Diproses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="Pesanan Siap" {{ $o->status == 'Pesanan Siap' ? 'selected' : '' }}>Pesanan Siap</option>
                                    <option value="Sedang Diantar" {{ $o->status == 'Sedang Diantar' ? 'selected' : '' }}>Sedang Diantar</option>
                                    <option value="Selesai" {{ $o->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="Dibatalkan" {{ $o->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </form>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-2">
                            @if(($o->payment_status ?? 'Lunas') !== 'Lunas')
                                <form action="{{ route('kasir.pesanan.confirm-payment') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="group_id" value="{{ $o->group_id }}">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors">
                                        Konfirmasi Pembayaran
                                    </button>
                                </form>
                            @endif
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
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-10 text-slate-400">Belum ada pesanan untuk outlet ini.</td>
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
