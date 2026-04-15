@extends('admin.layouts.app')
@section('page_title', 'Data Pesanan')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Seluruh Pesanan</h2>
            <span class="text-xs text-slate-400">Kelola dan ubah status pesanan pelanggan</span>
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
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6 font-medium max-w-[200px] truncate" title="{{ $o->items_summary }}">{{ $o->items_summary }}</td>
                    <td class="py-3.5 px-6 font-semibold text-slate-900">Rp {{ number_format($o->total_harga, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-6">{{ $o->jenis_belanja }}</td>
                    <td class="py-3.5 px-6">
                        @if(($o->payment_status ?? 'Lunas') === 'Lunas')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-600 border border-green-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Lunas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $o->payment_status }}
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">
                        @if(($o->payment_status ?? 'Lunas') !== 'Lunas')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Menunggu Konfirmasi
                            </span>
                        @else
                            <form action="{{ route('admin.pesanan.status') }}" method="POST" class="inline-block">
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
                                <form action="{{ route('admin.pesanan.confirm-payment') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="group_id" value="{{ $o->group_id }}">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors">
                                        Konfirmasi Pembayaran
                                    </button>
                                </form>
                            @endif
                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition-colors" onclick="alert('Order ID: {{ $o->order_code ?? 'LEGACY' }}\nPembayaran: {{ $o->payment_status ?? 'Lunas' }}\nAlamat: {{ $o->alamat }}\nWaktu: {{ $o->created_at->format('d/m/Y H:i') }}')">
                                Detail
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-10 text-slate-400">Belum ada data pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
