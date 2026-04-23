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
