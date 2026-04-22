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
                    <td class="py-3.5 px-6 font-medium max-w-[200px] truncate" title="{{ $o->items_summary }}">{{ $o->items_summary }}</td>
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
                    {{-- Kolom Bukti Bayar --}}
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/></svg>
                                Detail
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-10 text-slate-400">Belum ada data pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .order-detail-popup {
        width: min(560px, calc(100vw - 24px)) !important;
        padding: 0 !important;
        border-radius: 30px !important;
        overflow: hidden !important;
        background:
            radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 34%),
            linear-gradient(180deg, #fff9f1 0%, #ffffff 50%, #fffdf8 100%) !important;
        box-shadow: 0 32px 90px rgba(15, 23, 42, 0.24) !important;
    }

    .order-detail-title {
        margin: 0 !important;
        padding: 28px 28px 0 !important;
        color: #0f172a !important;
        font-size: 1.3rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
    }

    .order-detail-html {
        margin: 0 !important;
        padding: 18px 28px 24px !important;
    }

    .order-detail-close {
        top: 18px !important;
        right: 18px !important;
        color: #94a3b8 !important;
        font-size: 1.85rem !important;
        transition: color 0.18s ease, transform 0.18s ease !important;
    }

    .order-detail-close:hover {
        color: #334155 !important;
        transform: scale(1.06);
    }

    .order-detail-shell {
        display: flex;
        flex-direction: column;
        gap: 16px;
        color: #0f172a;
        text-align: left;
    }

    .order-detail-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 20px;
        background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 58%, #ef4444 100%);
        color: #ffffff;
        box-shadow: 0 20px 50px rgba(185, 28, 28, 0.32);
    }

    .order-detail-hero::before,
    .order-detail-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .order-detail-hero::before {
        width: 170px;
        height: 170px;
        top: -78px;
        right: -54px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 72%);
    }

    .order-detail-hero::after {
        width: 110px;
        height: 110px;
        bottom: -48px;
        left: -24px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0) 74%);
    }

    .order-detail-hero-top,
    .order-detail-hero-badges {
        position: relative;
        z-index: 1;
    }

    .order-detail-hero-top {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .order-detail-hero-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
    }

    .order-detail-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: rgba(255, 255, 255, 0.72);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .order-detail-order-code {
        display: block;
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.1;
    }

    .order-detail-hero-copy {
        margin-top: 8px;
        max-width: 360px;
        color: rgba(255, 255, 255, 0.82);
        font-size: 12.5px;
        line-height: 1.5;
    }

    .order-detail-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .order-detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        backdrop-filter: blur(8px);
    }

    .order-detail-badge::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.08);
    }

    .order-detail-badge--success {
        color: #ecfdf5;
        background: rgba(22, 163, 74, 0.2);
    }

    .order-detail-badge--warning {
        color: #fef3c7;
        background: rgba(245, 158, 11, 0.22);
    }

    .order-detail-badge--info {
        color: #e0f2fe;
        background: rgba(14, 165, 233, 0.18);
    }

    .order-detail-badge--neutral {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.12);
    }

    .order-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(220px, 0.88fr);
        gap: 14px;
    }

    .order-detail-stack,
    .order-detail-metrics {
        display: grid;
        gap: 12px;
    }

    .order-detail-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .order-detail-card {
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.88);
        padding: 15px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }

    .order-detail-card--subtle {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .order-detail-card--status {
        background: linear-gradient(135deg, rgba(255, 251, 235, 0.96), rgba(255, 255, 255, 0.98));
        border-color: #fde68a;
    }

    .order-detail-card--paid {
        background: linear-gradient(135deg, rgba(240, 253, 244, 0.98), rgba(255, 255, 255, 0.98));
        border-color: #bbf7d0;
    }

    .order-detail-card--price {
        background: linear-gradient(135deg, rgba(255, 241, 242, 0.95), rgba(255, 247, 237, 0.96));
        border-color: #fecdd3;
    }

    .order-detail-card-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .order-detail-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .order-detail-icon-wrap--blue {
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        color: #2563eb;
    }

    .order-detail-icon-wrap--amber {
        background: linear-gradient(135deg, #fef3c7, #fff7ed);
        color: #d97706;
    }

    .order-detail-icon-wrap--emerald {
        background: linear-gradient(135deg, #dcfce7, #f0fdf4);
        color: #16a34a;
    }

    .order-detail-icon-wrap--rose {
        background: linear-gradient(135deg, #ffe4e6, #fff1f2);
        color: #e11d48;
    }

    .order-detail-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .order-detail-value {
        margin-top: 4px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.4;
        word-break: break-word;
    }

    .order-detail-value--status {
        font-size: 18px;
    }

    .order-detail-value--price {
        font-size: 1.55rem;
        color: #be123c;
        letter-spacing: -0.02em;
    }

    .order-detail-caption {
        margin-top: 4px;
        color: #64748b;
        font-size: 12.5px;
        line-height: 1.5;
        word-break: break-word;
    }

    .order-detail-metric--wide {
        grid-column: 1 / -1;
    }

    .order-detail-confirm {
        width: calc(100% - 56px);
        margin: 0 28px 28px !important;
        border: 0;
        border-radius: 18px;
        background: linear-gradient(135deg, #991b1b, #dc2626);
        color: #ffffff;
        font-size: 0.96rem;
        font-weight: 700;
        padding: 14px 18px;
        box-shadow: 0 16px 32px rgba(185, 28, 28, 0.24);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .order-detail-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 36px rgba(185, 28, 28, 0.28);
    }

    .order-detail-confirm:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.22), 0 18px 36px rgba(185, 28, 28, 0.28);
    }

    @media (max-width: 640px) {
        .order-detail-title {
            padding: 24px 22px 0 !important;
        }

        .order-detail-html {
            padding: 16px 22px 22px !important;
        }

        .order-detail-grid,
        .order-detail-metrics {
            grid-template-columns: 1fr;
        }

        .order-detail-confirm {
            width: calc(100% - 44px);
            margin: 0 22px 22px !important;
        }
    }

    @media (max-width: 520px) {
        .order-detail-hero {
            padding: 18px;
        }

        .order-detail-order-code {
            font-size: 1.32rem;
        }

        .order-detail-card {
            padding: 14px;
        }
    }
</style>
<script>
function escapeOrderDetailHtml(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showOrderDetail(data) {
    const safe = {
        order_code: escapeOrderDetailHtml(data.order_code),
        outlet_label: escapeOrderDetailHtml(data.outlet_label),
        payment_method_label: escapeOrderDetailHtml(data.payment_method_label),
        payment_status: escapeOrderDetailHtml(data.payment_status),
        alamat: escapeOrderDetailHtml(data.alamat),
        outlet_address: escapeOrderDetailHtml(data.outlet_address),
        waktu: escapeOrderDetailHtml(data.waktu),
        nama_pelanggan: escapeOrderDetailHtml(data.nama_pelanggan),
        no_hp: escapeOrderDetailHtml(data.no_hp),
        jenis_belanja: escapeOrderDetailHtml(data.jenis_belanja),
        total_harga: escapeOrderDetailHtml(data.total_harga),
        payment_proof_url: data.payment_proof_url || '',
        payment_proof_uploaded_at: escapeOrderDetailHtml(data.payment_proof_uploaded_at || ''),
    };

    const paymentStatus = String(data.payment_status || '').trim().toLowerCase();
    const orderType = String(data.jenis_belanja || '').trim().toLowerCase();
    const isPaid = paymentStatus === 'lunas';
    const isDelivery = orderType === 'delivery';

    const paymentBadgeClass = isPaid ? 'order-detail-badge--success' : 'order-detail-badge--warning';
    const typeBadgeClass = isDelivery ? 'order-detail-badge--info' : 'order-detail-badge--neutral';
    const paymentCardClass = isPaid ? 'order-detail-card--paid' : 'order-detail-card--status';

    Swal.fire({
        titleText: 'Detail Pesanan',
        html: `
            <div class="order-detail-shell">
                <div class="order-detail-hero">
                    <div class="order-detail-hero-top">
                        <div class="order-detail-hero-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <span class="order-detail-eyebrow">Ringkasan Transaksi</span>
                            <strong class="order-detail-order-code">${safe.order_code}</strong>
                            <p class="order-detail-hero-copy">Cek identitas pelanggan, outlet, dan status pembayaran sebelum pesanan diproses lebih lanjut.</p>
                        </div>
                    </div>
                    <div class="order-detail-hero-badges">
                        <span class="order-detail-badge ${paymentBadgeClass}">${safe.payment_status}</span>
                        <span class="order-detail-badge ${typeBadgeClass}">${safe.jenis_belanja}</span>
                    </div>
                </div>

                <div class="order-detail-grid">
                    <div class="order-detail-stack">
                        <div class="order-detail-card order-detail-card--subtle">
                            <div class="order-detail-card-head">
                                <div class="order-detail-icon-wrap order-detail-icon-wrap--blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="order-detail-label">Pelanggan</div>
                                    <div class="order-detail-value">${safe.nama_pelanggan}</div>
                                    <div class="order-detail-caption">${safe.no_hp}</div>
                                </div>
                            </div>
                        </div>

                        <div class="order-detail-card order-detail-card--subtle">
                            <div class="order-detail-card-head">
                                <div class="order-detail-icon-wrap order-detail-icon-wrap--amber">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="order-detail-label">Outlet</div>
                                    <div class="order-detail-value">${safe.outlet_label}</div>
                                    <div class="order-detail-caption">${safe.outlet_address}</div>
                                </div>
                            </div>
                        </div>

                        <div class="order-detail-card order-detail-card--subtle">
                            <div class="order-detail-card-head">
                                <div class="order-detail-icon-wrap order-detail-icon-wrap--emerald">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="order-detail-label">Alamat Pelanggan</div>
                                    <div class="order-detail-value">${safe.alamat}</div>
                                    <div class="order-detail-caption">Gunakan alamat ini sebagai acuan pengantaran atau verifikasi pickup.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="order-detail-stack">
                        <div class="order-detail-card ${paymentCardClass}">
                            <div class="order-detail-card-head">
                                <div class="order-detail-icon-wrap order-detail-icon-wrap--rose">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2m-1 0h12a1 1 0 011 1v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8a1 1 0 011-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="order-detail-label">Pembayaran</div>
                                    <div class="order-detail-value order-detail-value--status">${safe.payment_status}</div>
                                    <div class="order-detail-caption">Metode ${safe.payment_method_label}</div>
                                </div>
                            </div>
                        </div>

                        <div class="order-detail-metrics">
                            <div class="order-detail-card order-detail-card--subtle">
                                <div class="order-detail-label">Metode</div>
                                <div class="order-detail-value">${safe.payment_method_label}</div>
                                <div class="order-detail-caption">Jenis pembayaran yang dipilih pelanggan.</div>
                            </div>

                            <div class="order-detail-card order-detail-card--subtle">
                                <div class="order-detail-label">Waktu</div>
                                <div class="order-detail-value">${safe.waktu}</div>
                                <div class="order-detail-caption">Timestamp saat order masuk ke sistem.</div>
                            </div>

                            <div class="order-detail-card order-detail-card--price order-detail-metric--wide">
                                <div class="order-detail-label">Total Tagihan</div>
                                <div class="order-detail-value order-detail-value--price">Rp ${safe.total_harga}</div>
                                <div class="order-detail-caption">Nominal akhir yang tercatat untuk transaksi ini.</div>
                            </div>
                        </div>
                    </div>
                </div>

                ${safe.payment_proof_url ? `
                <div class="order-detail-card" style="border-color:#bbf7d0;background:linear-gradient(135deg,#f0fdf4,#fff);">
                    <div class="order-detail-label" style="margin-bottom:10px;">Bukti Pembayaran</div>
                    <div style="display:flex;align-items:flex-start;gap:14px;">
                        <a href="${safe.payment_proof_url}" target="_blank" rel="noopener" style="display:block;flex-shrink:0;">
                            <img src="${safe.payment_proof_url}" alt="Bukti Bayar"
                                style="width:100px;height:100px;object-fit:cover;border-radius:14px;border:2px solid #86efac;box-shadow:0 6px 16px rgba(22,163,74,0.18);cursor:pointer;transition:transform 0.2s;"
                                onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform=''">
                        </a>
                        <div>
                            <div class="order-detail-value" style="color:#15803d;font-size:14px;">Bukti sudah diupload</div>
                            ${safe.payment_proof_uploaded_at ? `<div class="order-detail-caption">Diunggah pada: ${safe.payment_proof_uploaded_at}</div>` : ''}
                            <a href="${safe.payment_proof_url}" target="_blank" rel="noopener"
                                style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:6px 12px;background:#dcfce7;border:1px solid #86efac;border-radius:8px;color:#15803d;font-size:12px;font-weight:700;text-decoration:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Buka Full Size
                            </a>
                        </div>
                    </div>
                </div>` : `
                <div class="order-detail-card" style="border-color:#fde68a;background:linear-gradient(135deg,#fffbeb,#fff);">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="order-detail-label">Bukti Pembayaran</div>
                            <div style="font-size:13px;color:#b45309;font-weight:600;margin-top:2px;">Belum ada bukti yang diupload</div>
                            <div class="order-detail-caption">Pelanggan belum mengirimkan foto bukti pembayaran.</div>
                        </div>
                    </div>
                </div>`}
            </div>
        `,
        showConfirmButton: true,
        showCloseButton: true,
        confirmButtonText: 'Tutup',
        width: '560px',
        padding: '0',
        buttonsStyling: false,
        customClass: {
            popup: 'order-detail-popup',
            title: 'order-detail-title',
            htmlContainer: 'order-detail-html',
            confirmButton: 'order-detail-confirm',
            closeButton: 'order-detail-close',
        },
        showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
        hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' },
    });
}

function showProofImage(imageUrl, orderCode) {
    Swal.fire({
        titleText: `Bukti Bayar - ${orderCode}`,
        html: `
            <div style="text-align:center;">
                <img src="${imageUrl}" alt="Bukti Pembayaran"
                    style="max-width:100%;max-height:70vh;object-fit:contain;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.15);">
                <div style="margin-top:12px;">
                    <a href="${imageUrl}" target="_blank" rel="noopener"
                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;color:#15803d;font-size:13px;font-weight:700;text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka di Tab Baru
                    </a>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#64748b',
        width: 'auto',
        padding: '20px',
    });
}
</script>
@endpush
