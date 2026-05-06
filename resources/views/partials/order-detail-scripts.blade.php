<style>
    .order-detail-popup {
        width: 100% !important;
        max-width: 480px !important;
        padding: 0 !important;
        border-radius: 20px !important;
        overflow: hidden !important;
        background: #ffffff !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        font-family: inherit !important;
    }
    .order-detail-html { margin: 0 !important; padding: 0 !important; text-align: left !important; }
    .order-detail-close { top: 16px !important; right: 16px !important; color: #94a3b8 !important; }
    .order-detail-close:hover { color: #0f172a !important; background: transparent !important; }
    .order-detail-confirm {
        width: calc(100% - 48px);
        margin: 0 24px 24px !important;
        border-radius: 12px;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.95rem;
        font-weight: 600;
        padding: 12px 16px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .order-detail-confirm:hover { background: #e2e8f0; color: #0f172a; }
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

    const isPaid = String(data.payment_status || '').trim().toLowerCase() === 'lunas';
    const paymentBadgeClass = isPaid ? 'background:#dcfce7;color:#16a34a;' : 'background:#fef3c7;color:#d97706;';
    const typeBadgeClass = 'background:#f1f5f9;color:#475569;';

    const htmlContent = `
        <div>
            <!-- Header -->
            <div style="padding: 24px 24px 20px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Pesanan #${safe.order_code}</h3>
                <p style="margin: 4px 0 14px; font-size: 0.875rem; color: #64748b;">${safe.waktu}</p>
                <div style="display: flex; gap: 8px;">
                    <span style="${paymentBadgeClass} padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">${safe.payment_status}</span>
                    <span style="${typeBadgeClass} padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">${safe.jenis_belanja}</span>
                </div>
            </div>
            
            <!-- Body -->
            <div style="padding: 24px;">
                <div style="margin-bottom: 24px;">
                    <h4 style="margin: 0 0 12px; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Informasi Pelanggan</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Nama</span>
                            <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b;">${safe.nama_pelanggan}</span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Nomor HP</span>
                            <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b;">${safe.no_hp}</span>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <span style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Outlet & Alamat</span>
                            <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b;">${safe.outlet_label}</span>
                            <span style="display: block; font-size: 0.85rem; color: #475569; margin-top: 4px; line-height: 1.4;">${safe.alamat}</span>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <h4 style="margin: 0 0 12px; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Informasi Pembayaran</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Metode</span>
                            <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b;">${safe.payment_method_label}</span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Total Tagihan</span>
                            <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #059669;">Rp ${safe.total_harga}</span>
                        </div>
                    </div>
                </div>

                ${safe.payment_proof_url ? `
                <div style="display: flex; align-items: center; gap: 16px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                    <img src="${safe.payment_proof_url}" style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" alt="Bukti">
                    <div>
                        <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b;">Bukti Tersedia</span>
                        <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 2px;">Diunggah ${safe.payment_proof_uploaded_at}</span>
                        <a href="${safe.payment_proof_url}" target="_blank" style="display: inline-block; margin-top: 6px; font-size: 0.8rem; font-weight: 600; color: #2563eb; text-decoration: none;">Lihat Gambar &rarr;</a>
                    </div>
                </div>
                ` : `
                <div style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;">
                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.9rem; font-weight: 600; color: #b45309;">Belum Upload Bukti</span>
                        <span style="display: block; font-size: 0.8rem; color: #d97706; margin-top: 4px; line-height: 1.4;">Pelanggan belum mengirimkan foto bukti pembayaran.</span>
                    </div>
                </div>
                `}
            </div>
        </div>
    `;

    Swal.fire({
        html: htmlContent,
        showConfirmButton: true,
        showCloseButton: true,
        confirmButtonText: 'Tutup',
        width: '480px',
        padding: '0',
        buttonsStyling: false,
        customClass: {
            popup: 'order-detail-popup',
            htmlContainer: 'order-detail-html',
            confirmButton: 'order-detail-confirm',
            closeButton: 'order-detail-close',
        }
    });
}

function showProofImage(imageUrl, orderCode) {
    Swal.fire({
        titleText: `Bukti Bayar - ${orderCode}`,
        html: `
            <div style="text-align:center;">
                <img src="${imageUrl}" alt="Bukti Pembayaran" style="max-width:100%;max-height:70vh;object-fit:contain;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
                <div style="margin-top:16px;">
                    <a href="${imageUrl}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f1f5f9;border-radius:10px;color:#0f172a;font-size:14px;font-weight:600;text-decoration:none;">
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
