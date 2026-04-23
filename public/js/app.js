// ========================================================================
// Chi-Pok App - Aplikasi Utama (Versi Laravel)
// ========================================================================
//
// File ini adalah OTAK utama dari aplikasi Chi-Pok.
// Semua logika JavaScript untuk halaman utama (home) ada di sini:
//
// FITUR YANG DIKELOLA:
// 1. CART (Keranjang Belanja) - Tambah, hapus, ubah jumlah item
// 2. AUTH (Autentikasi) - Login, Register, Logout
// 3. PRODUCT (Produk) - Render menu, filter kategori, carousel
// 4. ADMIN - Panel admin, kelola pesanan & menu
// 5. REVIEW - Sistem ulasan/rating produk
// 6. UI - Modal, drawer mobile, smooth scroll, animasi
//
// DATA:
// - Produk di-pass dari server Laravel via Blade template (@json)
// - Auth & CRUD menggunakan AJAX (fetch API) ke Laravel routes
// - Cart disimpan di localStorage browser (tetap ada walau refresh)
// ========================================================================

// ====================================================================
// VARIABEL GLOBAL
// ====================================================================

// Data produk dari server (di-inject melalui Blade template di home.blade.php)
let products = PRODUCTS_DATA;

// Data user yang sedang login (null = belum login)
let currentUser = null;

// Kategori menu yang sedang aktif ('semua', 'makanan', 'minuman')
let currentCategory = 'semua';

// Halaman carousel saat ini (untuk pagination menu di halaman utama)
let carouselPage = 0;

// Jumlah item yang ditampilkan per halaman carousel
const ITEMS_PER_PAGE = 4;

// ========================================================================
// SISTEM KERANJANG BELANJA (CART)
// ========================================================================
//
// Cart disimpan di localStorage browser, artinya:
// - Data cart TETAP ADA walau halaman di-refresh
// - Data cart HILANG jika browser cache dihapus
// - Setiap perubahan cart harus disimpan dengan saveCart()
//
// Struktur item di cart:
// { id: number, name: string, price: number, image: string, qty: number }
// ========================================================================

// Ambil data cart dari localStorage, atau array kosong jika belum ada
// JSON.parse() = mengubah string JSON menjadi object/array JavaScript
let cart = JSON.parse(localStorage.getItem('chipok_cart') || '[]');

/**
 * saveCart() - Simpan cart ke localStorage
 *
 * Dipanggil setiap kali ada perubahan pada cart.
 * JSON.stringify() = mengubah array JavaScript menjadi string JSON
 * agar bisa disimpan di localStorage (hanya menerima string).
 */
function saveCart() {
    localStorage.setItem('chipok_cart', JSON.stringify(cart));
}

/**
 * addToCart(productId) - Tambahkan produk ke keranjang
 * @param {number} productId - ID produk yang ingin ditambahkan
 *
 * Cara kerja:
 * 1. Cari produk berdasarkan ID
 * 2. Jika produk sudah ada di cart → tambah qty (jumlah) +1
 * 3. Jika belum ada → buat item baru dengan qty = 1
 * 4. Simpan cart, update badge, dan tampilkan notifikasi
 */
function addToCart(productId) {
    // Cari data produk dari array products berdasarkan ID
    const product = products.find(p => p.id === productId);
    if (!product) return; // Jika produk tidak ditemukan, hentikan

    // Cek apakah produk sudah ada di keranjang
    const existing = cart.find(item => item.id === productId);

    if (existing) {
        // Produk sudah ada → tambah jumlahnya saja
        existing.qty += 1;
    } else {
        // Produk belum ada → tambahkan sebagai item baru
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            qty: 1
        });
    }

    saveCart();                          // Simpan perubahan ke localStorage
    updateCartBadge();                   // Update angka di icon keranjang
    showCartNotification(product.name);  // Tampilkan notifikasi "ditambahkan ke keranjang!"
}

/**
 * removeFromCart(productId) - Hapus produk dari keranjang
 * @param {number} productId - ID produk yang ingin dihapus
 *
 * filter() membuat array baru TANPA item yang ID-nya cocok
 */
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartBadge();
    renderCartModal(); // Render ulang tampilan cart
}

/**
 * updateCartQty(productId, newQty) - Ubah jumlah item di keranjang
 * @param {number} productId - ID produk
 * @param {number} newQty - Jumlah baru yang diinginkan
 *
 * Jika jumlah baru <= 0, hapus item dari cart
 */
function updateCartQty(productId, newQty) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    if (newQty <= 0) {
        removeFromCart(productId); // Hapus jika jumlah 0 atau kurang
        return;
    }
    item.qty = newQty;
    saveCart();
    renderCartModal();
}

/**
 * getCartTotal() - Hitung total harga semua item di keranjang
 * @returns {number} - Total harga (harga × jumlah untuk setiap item)
 *
 * reduce() menjumlahkan semua (price × qty) dari setiap item
 */
function getCartTotal() {
    return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
}

/**
 * getCartCount() - Hitung total jumlah item di keranjang
 * @returns {number} - Total qty dari semua item
 */
function getCartCount() {
    return cart.reduce((sum, item) => sum + item.qty, 0);
}

/**
 * clearCart() - Kosongkan seluruh keranjang
 */
function clearCart() {
    cart = [];
    saveCart();
    updateCartBadge();
    renderCartModal();
}

/**
 * showCartNotification(itemName) - Tampilkan notifikasi popup saat item ditambahkan
 * @param {string} itemName - Nama produk yang ditambahkan
 *
 * Membuat elemen div notifikasi di pojok kanan bawah layar,
 * lalu menghilangkannya setelah 2 detik dengan animasi slide-out.
 */
function showCartNotification(itemName) {
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        icon: 'success',
        title: `${itemName} ditambahkan ke keranjang!`
    });
}

// ========================================================================
// FUNGSI HELPER (PEMBANTU)
// ========================================================================

/**
 * formatRupiah(number) - Format angka menjadi mata uang Rupiah
 * @param {number} number - Angka yang ingin diformat
 * @returns {string} - Contoh: "Rp 25.000,00"
 */
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
}

/**
 * resolveAppUrl(path) - Normalisasi URL agar tetap benar meski aplikasi dijalankan di subfolder.
 *
 * Contoh kasus error yang sering terjadi:
 * - Aplikasi dibuka lewat: http://localhost/nama-folder/public
 * - Tetapi JavaScript memanggil endpoint absolut: /login
 *   -> jadi menuju http://localhost/login (SALAH) dan akhirnya error.
 *
 * Solusi: prefix semua request/asset dengan APP_BASE_URL yang di-inject dari Blade.
 */
function resolveAppUrl(path) {
    if (!path) return path;

    // Absolute URL (http/https) -> langsung pakai
    if (/^https?:\/\//i.test(path)) return path;

    const base = (typeof APP_BASE_URL !== 'undefined' && APP_BASE_URL)
        ? String(APP_BASE_URL).replace(/\/+$/, '')
        : '';

    // Jika tidak ada base URL (fallback), gunakan path apa adanya
    if (!base) return path;

    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    return base + normalizedPath;
}

/**
 * resolveAssetUrl(path) - Khusus untuk path asset (gambar, dll).
 * Memastikan path relatif selalu berubah jadi path absolut (diawali '/')
 * sebelum diprefix APP_BASE_URL.
 */
function resolveAssetUrl(path) {
    if (!path) return path;
    if (/^https?:\/\//i.test(path)) return path;
    const normalized = String(path).startsWith('/') ? String(path) : `/${path}`;
    return resolveAppUrl(normalized);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatPaymentMethodLabel(paymentMethod) {
    const labels = {
        qris: 'QRIS',
        bank_transfer: 'Transfer Bank',
        whatsapp_transfer: 'Transfer via WhatsApp',
        manual: 'Manual'
    };

    return labels[paymentMethod] || String(paymentMethod || '-').replace(/_/g, ' ');
}

function generateCheckoutRequestId() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID();
    }

    return `checkout-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
}

function getBackofficeDashboardPath(user) {
    if (!user || !user.role) {
        return null;
    }

    if (user.role === 'admin') {
        return '/admin/dashboard';
    }

    if (user.role === 'kasir') {
        return '/kasir/dashboard';
    }

    return null;
}

function getCheckoutPaymentHint(paymentMethod) {
    if (paymentMethod === 'bank_transfer') {
        return 'Mode demo transfer bank: tampilkan rekening simulasi tanpa transaksi sungguhan.';
    }

    return 'Mode demo QRIS: tampilkan QRIS simulasi tanpa transaksi sungguhan.';
}

function normalizeOrderType(orderType) {
    return String(orderType || '').trim().toLowerCase().replace(/\s+/g, '-');
}

function normalizeSearchText(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();
}

function buildOutletArea(outlet) {
    return [outlet?.district, outlet?.city, outlet?.province]
        .filter(Boolean)
        .join(', ');
}

function buildOutletLabel(outlet) {
    const area = buildOutletArea(outlet);
    return area ? `${outlet.name} - ${area}` : (outlet?.name || 'Outlet');
}

function buildPaymentDetailsHtml(result) {
    const method = result.payment_method || '';
    const methodLabel = escapeHtml(result.payment_method_label || formatPaymentMethodLabel(method));
    const orderCode = escapeHtml(result.order_code || '-');
    const total = formatRupiah(Number(result.payment_total || 0));
    const details = result.payment_details || {};
    const instructions = Array.isArray(result.payment_instructions) ? result.payment_instructions : [];
    const adminWhatsapp = escapeHtml(result.admin_whatsapp || '-');
    const selectedOutlet = result.outlet || null;
    const outletLabel = selectedOutlet ? escapeHtml(selectedOutlet.label || buildOutletLabel(selectedOutlet)) : '';
    const outletAddress = selectedOutlet?.address ? escapeHtml(selectedOutlet.address) : '';
    let detailBlock = '';

    if (method === 'qris') {
        const qrisLabel = escapeHtml(details.label || 'QRIS');
        const qrisNote = details.note ? `<p class="mt-2 text-xs text-gray-500 leading-5">${escapeHtml(details.note)}</p>` : '';
        const qrisImageUrl = details.image_url ? resolveAssetUrl(details.image_url) : '';

        detailBlock = `
            <div class="mt-4 rounded-xl border border-red-100 bg-red-50 p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-red-500 mb-2">${qrisLabel}</p>
                ${qrisImageUrl
                    ? `<img src="${qrisImageUrl}" alt="${qrisLabel}" class="mx-auto w-full max-w-[240px] rounded-xl border border-red-100 bg-white p-3">`
                    : '<div class="rounded-xl border border-dashed border-red-200 bg-white px-4 py-6 text-center text-sm text-gray-500"><div class="mx-auto mb-2 flex h-24 w-24 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-xs font-bold text-red-500">QRIS DEMO</div>Tampilan ini adalah placeholder QRIS demo untuk simulasi pembayaran.</div>'}
                ${qrisNote}
            </div>
        `;
    }

    if (method === 'bank_transfer') {
        detailBlock = `
            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4 text-left">
                <div class="grid gap-2 text-sm text-gray-700">
                    <p><b>Bank:</b> ${escapeHtml(details.bank_name || '-')}</p>
                    <p><b>No. Rekening:</b> ${escapeHtml(details.account_number || '-')}</p>
                    <p><b>Atas Nama:</b> ${escapeHtml(details.account_name || '-')}</p>
                </div>
                ${details.note ? `<p class="mt-3 text-xs text-gray-500 leading-5">${escapeHtml(details.note)}</p>` : ''}
            </div>
        `;
    }

    return `
        <div class="text-sm text-gray-600 leading-6 text-left">
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                <b>Mode Demo:</b> pembayaran ini hanya simulasi dan belum terhubung ke gateway live.
            </div>
            <p><b>Kode Order:</b> ${orderCode}</p>
            <p><b>Metode Pembayaran:</b> ${methodLabel}</p>
            <p><b>Total Bayar:</b> ${total}</p>
            ${selectedOutlet ? `
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Outlet Pesanan</p>
                    <p class="text-sm font-semibold text-slate-900">${outletLabel}</p>
                    ${outletAddress ? `<p class="mt-1 text-xs text-slate-500 leading-5">${outletAddress}</p>` : ''}
                </div>
            ` : ''}
            ${result.admin_whatsapp ? `<p><b>WA Admin:</b> ${adminWhatsapp}</p>` : ''}
            ${detailBlock}
            ${instructions.length ? `
                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Instruksi Pembayaran</p>
                    <ul class="list-disc pl-5 space-y-1">
                        ${instructions.map((instruction) => `<li>${escapeHtml(instruction)}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

/**
 * apiRequest(url, method, data) - Fungsi untuk mengirim AJAX request ke server
 * @param {string} url - URL endpoint API (contoh: '/login', '/products')
 * @param {string} method - HTTP method ('GET', 'POST', 'PUT', 'DELETE')
 * @param {object} data - Data yang dikirim (untuk POST/PUT)
 * @returns {Promise<object>} - Response dari server dalam format JSON
 *
 * PENTING:
 * - 'X-CSRF-TOKEN' wajib ada untuk keamanan Laravel (mencegah serangan CSRF)
 * - 'credentials: same-origin' agar cookie session ikut dikirim
 * - async/await digunakan agar kode lebih mudah dibaca (tidak callback hell)
 */
async function apiRequest(url, method = 'GET', data = null, reqOptions = {}) {
    // Ambil CSRF token terbaru dari meta tag (untuk menghindari token expired)
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute('content') : CSRF_TOKEN;

    const fetchOpts = {
        method,
        headers: {
            'Content-Type': 'application/json',    // Beritahu server bahwa data berformat JSON
            'X-CSRF-TOKEN': token,                  // Token keamanan Laravel (dari meta tag)
            'Accept': 'application/json',           // Minta response dalam format JSON
        },
        credentials: 'same-origin',                // Kirim cookie session agar auth bekerja
    };

    // Jika ada data, ubah menjadi string JSON dan masukkan ke body request
    if (data) fetchOpts.body = JSON.stringify(data);

    // Kirim request dan tunggu response
    const response = await fetch(resolveAppUrl(url), fetchOpts);

    // Jika CSRF token expired (419), tangani berdasarkan mode silent/tidak
    if (response.status === 419) {
        // Mode SILENT: untuk request background (polling, dll)
        // Tidak perlu tampilkan pop-up, cukup throw error agar catch block menangani
        if (reqOptions.silent) {
            throw new Error('CSRF token expired');
        }
        // Mode NORMAL: tampilkan pop-up dan reload halaman
        Swal.fire({
            icon: 'warning',
            title: 'Sesi Habis',
            text: 'Kamu terlalu lama di halaman ini tanpa aktivitas. Halaman akan dimuat ulang, silakan coba lagi.',
            confirmButtonColor: '#D20000',
            confirmButtonText: 'Muat Ulang'
        }).then(() => {
            window.location.reload();
        });
        throw new Error('CSRF token expired');
    }

    // Parse response menjadi object JavaScript
    let result;
    try {
        result = await response.json();
    } catch (e) {
        // Jika response bukan JSON (misalnya HTML error page)
        return {
            success: false,
            message: 'Server mengembalikan response yang tidak valid. Pastikan URL aplikasi benar dan server Laravel berjalan.'
        };
    }

    // Jika response status bukan OK (bukan 2xx), format ulang sebagai error
    if (!response.ok) {
        // Untuk 422 (validation error), ambil pesan error pertama
        if (response.status === 422 && result.errors) {
            const firstField = Object.keys(result.errors)[0];
            const firstMessage = result.errors[firstField][0];
            return { success: false, message: firstMessage };
        }
        // Untuk error lain (401, 403, 500, dll)
        return { success: false, message: result.message || 'Terjadi kesalahan pada server.' };
    }

    return result;
}

// ========================================================================
// DOM READY - Kode utama berjalan setelah halaman selesai dimuat
// ========================================================================
// Semua kode di dalam event listener ini akan dijalankan SETELAH
// seluruh elemen HTML sudah siap (tombol, form, modal, dll sudah ada)
document.addEventListener("DOMContentLoaded", () => {

    // ====================================================================
    // AMBIL REFERENSI KE ELEMEN-ELEMEN HTML (by ID)
    // document.getElementById() mencari elemen berdasarkan atribut id="..."
    // ====================================================================

    const menuGrid = document.getElementById('menu-grid');         // Container grid menu produk
    const loginModal = document.getElementById('loginModal');       // Pop-up form login
    const signupModal = document.getElementById('signupModal');     // Pop-up form daftar akun
    const cartModal = document.getElementById('cartModal');         // Pop-up keranjang belanja
    const adminModal = document.getElementById('adminModal');       // Pop-up panel admin

    const btnLoginHeader = document.getElementById('btn-login');          // Tombol login/logout di header
    const btnCartHeader = document.getElementById('btn-cart-header');     // Tombol keranjang di header
    const btnAdminPanel = document.getElementById('btn-admin-panel');     // Tombol admin panel di navbar
    const navAdmin = document.getElementById('nav-admin');                // Menu admin di navbar (hidden by default)

    // Form-form yang ada di modal
    const loginForm = document.getElementById('loginForm');           // Form login
    const signupForm = document.getElementById('signupForm');         // Form pendaftaran
    const checkoutForm = document.getElementById('checkoutForm');     // Form checkout/pembayaran
    const addMenuForm = document.getElementById('addMenuForm');       // Form tambah menu (admin)

    // Elemen-elemen keranjang belanja
    const cartItemsContainer = document.getElementById('cart-items-container');   // Container daftar item di cart
    const cartTotalDisplay = document.getElementById('cart-total-display');       // Tampilan total harga
    const cartBadge = document.getElementById('cart-badge');                      // Badge angka di icon keranjang
    const btnCheckout = document.getElementById('btn-checkout');                  // Tombol checkout
    const btnClearCart = document.getElementById('btn-clear-cart');               // Tombol kosongkan keranjang
    const cartCheckoutSection = document.getElementById('cart-checkout-section'); // Section form checkout
    const cartActionButtons = document.getElementById('cart-action-buttons');     // Tombol aksi cart (checkout/kosongkan)
    const btnBackToCart = document.getElementById('btn-back-to-cart');            // Tombol kembali ke cart dari checkout
    const checkoutPaymentMethod = document.getElementById('checkout_payment_method');
    const checkoutPaymentHint = document.getElementById('checkout-payment-hint');
    const checkoutOutletSearch = document.getElementById('checkout_outlet_search');
    const checkoutOutletSelect = document.getElementById('checkout_outlet_id');
    const checkoutOutletHelper = document.getElementById('checkout-outlet-helper');
    const checkoutOutletPreview = document.getElementById('checkout-outlet-preview');
    const checkoutSubmitButton = checkoutForm ? checkoutForm.querySelector('button[type="submit"]') : null;
    const contactMapFrame = document.getElementById('contact-map-frame');
    const contactMapLink = document.getElementById('contact-map-link');
    const contactOutletCards = Array.from(document.querySelectorAll('[data-contact-outlet-card]'));

    // Elemen-elemen carousel (slider produk)
    const carouselPrev = document.getElementById('carousel-prev');    // Tombol prev carousel
    const carouselNext = document.getElementById('carousel-next');    // Tombol next carousel
    const carouselDots = document.getElementById('carousel-dots');    // Dots navigasi carousel
    let isCheckoutSubmitting = false;
    let activeCheckoutRequestId = null;

    if (checkoutSubmitButton) {
        checkoutSubmitButton.dataset.defaultLabel = checkoutSubmitButton.innerHTML;
    }

    function setCheckoutSubmittingState(isSubmitting) {
        isCheckoutSubmitting = isSubmitting;

        if (!checkoutSubmitButton) return;

        checkoutSubmitButton.disabled = isSubmitting;
        checkoutSubmitButton.innerHTML = isSubmitting
            ? '<i class="fas fa-spinner fa-spin"></i> Mengirim...'
            : (checkoutSubmitButton.dataset.defaultLabel || '<i class="fas fa-receipt"></i> Buat Pesanan');
    }

    // ====================================================================
    // UPDATE BADGE KERANJANG
    // ====================================================================

    /**
     * updateCartBadge() - Update angka badge di icon keranjang (header)
     *
     * Jika ada item di cart → tampilkan badge dengan jumlah item
     * Jika cart kosong → sembunyikan badge
     */
    function updateCartBadge() {
        const count = getCartCount();
        if (count > 0) {
            cartBadge.textContent = count;          // Set angka badge
            cartBadge.classList.remove('hidden');    // Tampilkan badge
            cartBadge.classList.add('flex');
        } else {
            cartBadge.classList.add('hidden');       // Sembunyikan badge
            cartBadge.classList.remove('flex');
        }
    }

    // Buat fungsi ini bisa diakses dari luar scope DOMContentLoaded
    // (diperlukan oleh fungsi addToCart() yang ada di scope global)
    window.updateCartBadge = updateCartBadge;

    function setActiveContactOutlet(card) {
        if (!card || !contactMapFrame || !contactMapLink) return;

        const embedUrl = card.dataset.outletEmbedUrl || contactMapFrame.src;
        const mapsUrl = card.dataset.outletMapsUrl || contactMapLink.href;
        const activeClasses = ['border-white/35', 'bg-white/18', 'shadow-lg', 'shadow-black/10'];
        const inactiveClasses = ['border-white/15', 'bg-white/10'];

        if (embedUrl) {
            contactMapFrame.src = embedUrl;
        }

        if (mapsUrl) {
            contactMapLink.href = mapsUrl;
        }

        contactOutletCards.forEach((item) => {
            const isActive = item === card;

            item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            item.classList.remove(...activeClasses, ...inactiveClasses);

            if (isActive) {
                item.classList.add(...activeClasses);
            } else {
                item.classList.add(...inactiveClasses);
            }
        });
    }

    if (contactOutletCards.length > 0 && contactMapFrame && contactMapLink) {
        contactOutletCards.forEach((card) => {
            const triggerButton = card.querySelector('[data-contact-outlet-trigger]');

            card.addEventListener('click', () => {
                setActiveContactOutlet(card);
            });

            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    setActiveContactOutlet(card);
                }
            });

            if (triggerButton) {
                triggerButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    setActiveContactOutlet(card);
                });
            }
        });

        const initialContactOutlet = contactOutletCards.find((card) => card.getAttribute('aria-pressed') === 'true')
            || contactOutletCards[0];

        setActiveContactOutlet(initialContactOutlet);
    }

    // Inisialisasi badge saat halaman pertama kali dimuat
    updateCartBadge();

    // ====================================================================
    // RENDER TAMPILAN KERANJANG BELANJA (di Modal)
    // ====================================================================

    /**
     * renderCartModal() - Render/tampilkan ulang isi modal keranjang belanja
     *
     * Fungsi ini dipanggil setiap kali cart berubah (tambah/hapus/ubah qty).
     * Menampilkan daftar item dengan gambar, nama, harga, tombol +/- qty,
     * dan tombol hapus. Juga update total harga di bagian bawah.
     */
    function renderCartModal() {
        if (!cartItemsContainer) return; // Jika elemen tidak ditemukan, hentikan

        // Jika keranjang kosong, tampilkan pesan kosong
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-shopping-basket text-4xl text-gray-300 mb-3 block"></i>
                    <p class="text-gray-400 text-sm">Keranjang belanja kosong</p>
                    <p class="text-gray-300 text-xs mt-1">Yuk tambahkan menu favoritmu!</p>
                </div>`;
            btnCheckout.disabled = true;                     // Nonaktifkan tombol checkout
            cartTotalDisplay.textContent = formatRupiah(0);  // Total = Rp 0
            return;
        }

        // Aktifkan tombol checkout dan kosongkan container
        btnCheckout.disabled = false;
        cartItemsContainer.innerHTML = '';

        // Loop setiap item di cart dan buat HTML-nya
        cart.forEach(item => {
            // Tentukan path gambar
            const imgSrc = resolveAssetUrl(item.image);
            // Hitung subtotal per item (harga x jumlah)
            const subtotal = item.price * item.qty;

            // Buat elemen HTML untuk item cart
            const el = document.createElement('div');
            el.className = 'flex items-center gap-3 bg-gray-50 rounded-xl p-3 border border-gray-100 transition hover:shadow-sm';
            el.innerHTML = `
                <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center p-1 shrink-0 border border-gray-100">
                    <img src="${imgSrc}" alt="${item.name}" class="w-full h-full object-contain">
                </div>
                <div class="flex-grow min-w-0">
                    <h4 class="font-bold text-sm text-gray-800 truncate">${item.name}</h4>
                    <p class="text-xs text-gray-500">${formatRupiah(item.price)}</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button class="cart-qty-btn w-7 h-7 rounded-full bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 flex items-center justify-center text-xs font-bold transition" data-id="${item.id}" data-action="minus">
                        <i class="fas fa-minus text-[10px]"></i>
                    </button>
                    <span class="w-8 text-center font-bold text-sm">${item.qty}</span>
                    <button class="cart-qty-btn w-7 h-7 rounded-full bg-gray-200 hover:bg-green-100 text-gray-600 hover:text-green-600 flex items-center justify-center text-xs font-bold transition" data-id="${item.id}" data-action="plus">
                        <i class="fas fa-plus text-[10px]"></i>
                    </button>
                </div>
                <div class="text-right shrink-0 w-24">
                    <p class="font-extrabold text-sm text-red-600">${formatRupiah(subtotal)}</p>
                </div>
                <button class="cart-remove-btn text-gray-300 hover:text-red-500 transition shrink-0" data-id="${item.id}" title="Hapus">
                    <i class="fas fa-times"></i>
                </button>`;
            cartItemsContainer.appendChild(el);
        });

        // Update tampilan total harga
        cartTotalDisplay.textContent = formatRupiah(getCartTotal());

        // Pasang event listener pada tombol +/- qty
        document.querySelectorAll('.cart-qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.id);       // Ambil ID produk dari data attribute
                const action = btn.dataset.action;          // 'plus' atau 'minus'
                const item = cart.find(i => i.id === id);
                if (!item) return;
                // Jika 'plus' → qty + 1, jika 'minus' → qty - 1
                updateCartQty(id, action === 'plus' ? item.qty + 1 : item.qty - 1);
                updateCartBadge();
            });
        });

        // Pasang event listener pada tombol hapus item (×)
        document.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                removeFromCart(parseInt(btn.dataset.id));
            });
        });
    }

    // Buat renderCartModal bisa diakses global
    window.renderCartModal = renderCartModal;

    // ====================================================================
    // BUKA/TUTUP MODAL KERANJANG
    // ====================================================================

    // Saat tombol keranjang/pesanan di header diklik
    btnCartHeader.addEventListener('click', () => {
        // Jika admin → buka panel admin (lihat pesanan)
        if (currentUser && currentUser.role === 'admin') {
            adminModal.classList.remove('hidden');
            renderOrdersTable();
            return;
        }
        // Jika user biasa → buka modal keranjang
        cartCheckoutSection.classList.add('hidden');
        cartActionButtons.classList.remove('hidden');
        renderCartModal();
        cartModal.classList.remove('hidden');
    });

    // Saat tombol × (close) diklik → tutup modal cart
    document.getElementById('closeCartModal').addEventListener('click', () => {
        cartModal.classList.add('hidden');
    });

    // Saat tombol "Kosongkan" diklik → konfirmasi lalu kosongkan cart
    btnClearCart.addEventListener('click', () => {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Hapus Semua?',
            text: "Apakah kamu yakin ingin mengosongkan keranjang lezatmu?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D20000',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                clearCart();
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2000,
                    icon: 'success',
                    title: 'Keranjang berhasil dikosongkan!'
                });
            }
        });
    });

    // ====================================================================
    // ALUR CHECKOUT (PEMBAYARAN/PEMESANAN)
    // ====================================================================
    // Alur: Klik Checkout → Cek Login → Tampilkan Form → Isi Data → Kirim Pesanan

    const availableOutlets = Array.isArray(typeof OUTLETS_DATA !== 'undefined' ? OUTLETS_DATA : [])
        ? OUTLETS_DATA
        : [];
    let hasManualOutletSelection = false;

    function getOutletMatchScore(outlet, queryText) {
        const query = normalizeSearchText(queryText);
        if (!query) return 0;

        const fields = [
            { value: normalizeSearchText(outlet.district), weight: 7 },
            { value: normalizeSearchText(outlet.city), weight: 5 },
            { value: normalizeSearchText(outlet.province), weight: 3 },
            { value: normalizeSearchText(outlet.name), weight: 2 },
            { value: normalizeSearchText(outlet.address), weight: 1 },
        ];

        return fields.reduce((score, field) => {
            if (!field.value) return score;
            if (query.includes(field.value)) return score + field.weight;
            if (query.length >= 3 && field.value.includes(query)) return score + Math.max(1, field.weight - 1);
            return score;
        }, 0);
    }

    function getRecommendedOutlet(queryText) {
        let bestOutlet = null;
        let bestScore = 0;

        availableOutlets.forEach((outlet) => {
            const score = getOutletMatchScore(outlet, queryText);
            if (score > bestScore) {
                bestScore = score;
                bestOutlet = outlet;
            }
        });

        return bestScore > 0 ? bestOutlet : null;
    }

    function getSelectedOutlet() {
        if (!checkoutOutletSelect) return null;
        const selectedId = Number(checkoutOutletSelect.value || 0);
        return availableOutlets.find((outlet) => outlet.id === selectedId) || null;
    }

    function renderOutletPreview(outlet) {
        if (!checkoutOutletPreview) return;

        if (!outlet) {
            checkoutOutletPreview.classList.add('hidden');
            checkoutOutletPreview.innerHTML = '';
            return;
        }

        const areaLabel = escapeHtml(buildOutletArea(outlet) || '-');
        const outletName = escapeHtml(outlet.name || 'Outlet');
        const outletAddress = escapeHtml(outlet.address || '-');
        const outletPhone = outlet.phone ? `<p class="mt-1 text-xs text-slate-500"><span class="font-semibold text-slate-600">Kontak:</span> ${escapeHtml(outlet.phone)}</p>` : '';
        const outletMaps = outlet.maps_url
            ? `<a href="${escapeHtml(outlet.maps_url)}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700">Buka Maps</a>`
            : '';

        checkoutOutletPreview.classList.remove('hidden');
        checkoutOutletPreview.innerHTML = `
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 mb-1">Outlet Terpilih</p>
            <p class="text-sm font-bold text-slate-900">${outletName}</p>
            <p class="mt-1 text-xs text-slate-500">${areaLabel}</p>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">${outletAddress}</p>
            ${outletPhone}
            ${outletMaps}
        `;
    }

    function updateOutletHelperText({ filterText = '', recommendedOutlet = null, filteredCount = 0 } = {}) {
        if (!checkoutOutletHelper) return;

        if (availableOutlets.length === 0) {
            checkoutOutletHelper.textContent = 'Belum ada outlet aktif yang tersedia. Silakan hubungi admin.';
            return;
        }

        if (filterText && filteredCount === 0) {
            checkoutOutletHelper.textContent = 'Tidak ada outlet yang cocok dengan pencarian. Coba kata kunci kota, kecamatan, atau nama outlet lain.';
            return;
        }

        if (recommendedOutlet) {
            checkoutOutletHelper.textContent = `Rekomendasi area kamu: ${buildOutletLabel(recommendedOutlet)}. Kamu tetap bisa memilih outlet lain jika mau.`;
            return;
        }

        checkoutOutletHelper.textContent = 'Pilih outlet yang paling dekat dengan area kamu.';
    }

    function renderOutletOptions(filterText = '', autoSelectRecommendation = false) {
        if (!checkoutOutletSelect) return;

        const normalizedFilter = normalizeSearchText(filterText);
        const previousValue = checkoutOutletSelect.value;
        const filteredOutlets = normalizedFilter
            ? availableOutlets.filter((outlet) => getOutletMatchScore(outlet, normalizedFilter) > 0)
            : availableOutlets;

        const displayedOutlets = filteredOutlets.length > 0 ? filteredOutlets : availableOutlets;
        const recommendedOutlet = getRecommendedOutlet(filterText);

        checkoutOutletSelect.disabled = availableOutlets.length === 0;
        checkoutOutletSelect.innerHTML = '<option value="">Pilih outlet terdekat</option>';

        displayedOutlets.forEach((outlet) => {
            const option = document.createElement('option');
            option.value = String(outlet.id);
            option.textContent = buildOutletLabel(outlet);
            checkoutOutletSelect.appendChild(option);
        });

        if (previousValue && displayedOutlets.some((outlet) => String(outlet.id) === previousValue)) {
            checkoutOutletSelect.value = previousValue;
        } else if (autoSelectRecommendation && recommendedOutlet) {
            checkoutOutletSelect.value = String(recommendedOutlet.id);
        } else {
            checkoutOutletSelect.value = '';
        }

        updateOutletHelperText({
            filterText,
            recommendedOutlet,
            filteredCount: filteredOutlets.length
        });
        renderOutletPreview(getSelectedOutlet());
    }

    function tryRecommendOutlet(sourceText) {
        if (!checkoutOutletSelect || hasManualOutletSelection) return;

        const recommendedOutlet = getRecommendedOutlet(sourceText);
        if (!recommendedOutlet) return;

        const optionExists = Array.from(checkoutOutletSelect.options).some((option) => option.value === String(recommendedOutlet.id));
        if (!optionExists) {
            renderOutletOptions(checkoutOutletSearch ? checkoutOutletSearch.value : '', false);
        }

        checkoutOutletSelect.value = String(recommendedOutlet.id);
        updateOutletHelperText({
            filterText: checkoutOutletSearch ? checkoutOutletSearch.value : '',
            recommendedOutlet,
            filteredCount: availableOutlets.length
        });
        renderOutletPreview(recommendedOutlet);
    }

    // Saat tombol "Checkout" diklik
    btnCheckout.addEventListener('click', () => {
        // Cek apakah user sudah login
        if (!currentUser) {
            Swal.fire({icon: 'info', title: 'Ups!', text: 'Kamu harus login dulu buat lanjutin pesanan lezatmu!', confirmButtonColor: '#D20000', confirmButtonText: 'Oke, Login!'}).then(() => {
                cartModal.classList.add('hidden');
                loginModal.classList.remove('hidden');
            });
            return;
        }

        // Ganti tampilan dari cart ke form checkout
        cartActionButtons.classList.add('hidden');        // Sembunyikan tombol cart
        cartCheckoutSection.classList.remove('hidden');   // Tampilkan form checkout

        // Pre-fill (isi otomatis) data user yang sudah login
        document.getElementById('checkout_nama').value = currentUser.name || '';
        document.getElementById('checkout_no_hp').value = currentUser.no_hp || '';
        if (checkoutPaymentMethod) {
            checkoutPaymentMethod.value = checkoutPaymentMethod.value || 'qris';
        }
        if (checkoutPaymentHint) {
            checkoutPaymentHint.textContent = getCheckoutPaymentHint(checkoutPaymentMethod ? checkoutPaymentMethod.value : 'qris');
        }
        hasManualOutletSelection = false;
        if (checkoutOutletSearch) {
            checkoutOutletSearch.value = '';
        }
        renderOutletOptions('', true);

        // Reset dan sesuaikan field alamat dengan jenis belanja yang dipilih saat ini
        toggleAlamatField();

        tryRecommendOutlet(
            checkoutJenis && checkoutJenis.value === 'Delivery'
                ? (alamatField.value || currentUser.alamat || '')
                : ''
        );
    });

    // Tombol "Kembali" dari checkout → tampilkan cart lagi
    btnBackToCart.addEventListener('click', () => {
        cartCheckoutSection.classList.add('hidden');      // Sembunyikan form checkout
        cartActionButtons.classList.remove('hidden');     // Tampilkan tombol cart
    });

    if (checkoutPaymentMethod && checkoutPaymentHint) {
        checkoutPaymentMethod.addEventListener('change', () => {
            checkoutPaymentHint.textContent = getCheckoutPaymentHint(checkoutPaymentMethod.value);
        });
    }

    // ====================================================================
    // TOGGLE FIELD ALAMAT BERDASARKAN JENIS BELANJA
    // ====================================================================
    // Take Away → alamat tidak perlu diisi
    // Delivery → alamat wajib diisi

    const checkoutJenis = document.getElementById('checkout_jenis');
    const alamatWrapper = document.getElementById('checkout-alamat-wrapper');
    const alamatField   = document.getElementById('checkout_alamat');
    const addressOptions = document.getElementById('address-options');
    const savedAddressPreview = document.getElementById('saved-address-preview');
    const useAddressCheckbox = document.getElementById('use_saved_address');

    function populateSavedAddressState() {
        if (!addressOptions || !savedAddressPreview || !useAddressCheckbox || !alamatField) return;

        if (currentUser && currentUser.alamat) {
            addressOptions.classList.remove('hidden');
            savedAddressPreview.textContent = currentUser.alamat.length > 50
                ? currentUser.alamat.substring(0, 50) + '...'
                : currentUser.alamat;
            useAddressCheckbox.checked = true;
            alamatField.value = currentUser.alamat;
            return;
        }

        addressOptions.classList.add('hidden');
        useAddressCheckbox.checked = false;
        alamatField.value = '';
    }

    function toggleAlamatField() {
        if (!checkoutJenis || !alamatWrapper || !alamatField) return;
        // Alamat hanya wajib diisi untuk Delivery
        const needsAddress = checkoutJenis.value === 'Delivery';
        if (!needsAddress) {
            alamatWrapper.classList.add('hidden');
            alamatField.removeAttribute('required');
            alamatField.value = '';
            if (addressOptions) addressOptions.classList.add('hidden');
            if (useAddressCheckbox) useAddressCheckbox.checked = false;
        } else {
            alamatWrapper.classList.remove('hidden');
            alamatField.setAttribute('required', 'required');
            populateSavedAddressState();
        }
    }

    if (checkoutJenis) {
        checkoutJenis.addEventListener('change', toggleAlamatField);
    }

    if (useAddressCheckbox && alamatField) {
        useAddressCheckbox.onchange = () => {
            if (useAddressCheckbox.checked && currentUser && currentUser.alamat) {
                alamatField.value = currentUser.alamat;
            } else {
                alamatField.value = '';
            }

            tryRecommendOutlet(alamatField.value);
        };
    }

    if (checkoutOutletSearch) {
        checkoutOutletSearch.addEventListener('input', () => {
            hasManualOutletSelection = false;
            renderOutletOptions(checkoutOutletSearch.value, true);
        });
    }

    if (checkoutOutletSelect) {
        checkoutOutletSelect.addEventListener('change', () => {
            hasManualOutletSelection = !!checkoutOutletSelect.value;
            renderOutletPreview(getSelectedOutlet());
        });
    }

    const checkoutAlamatField = document.getElementById('checkout_alamat');
    if (checkoutAlamatField) {
        checkoutAlamatField.addEventListener('input', () => {
            tryRecommendOutlet(checkoutAlamatField.value);
        });
    }

    // ====================================================================
    // KIRIM PESANAN (Submit Form Checkout)
    // ====================================================================
    // async karena menggunakan fetch API (AJAX request ke server)
    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Cegah form submit biasa (reload halaman)

        if (isCheckoutSubmitting) {
            return;
        }

        if (!checkoutForm.reportValidity()) {
            return;
        }

        if (availableOutlets.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Outlet Belum Tersedia',
                text: 'Saat ini belum ada outlet aktif yang bisa dipilih. Silakan hubungi admin dulu ya.',
                confirmButtonColor: '#D20000'
            });
            return;
        }

        if (checkoutOutletSelect && !checkoutOutletSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Outlet Belum Dipilih',
                text: 'Pilih outlet terlebih dahulu sebelum membuat pesanan ya.',
                confirmButtonColor: '#D20000'
            });
            return;
        }

        activeCheckoutRequestId = activeCheckoutRequestId || generateCheckoutRequestId();
        setCheckoutSubmittingState(true);

        // Kumpulkan data dari form checkout
        const data = {
            nama: document.getElementById('checkout_nama').value,
            no_hp: document.getElementById('checkout_no_hp').value,
            alamat: document.getElementById('checkout_alamat').value,
            jenis_belanja: document.getElementById('checkout_jenis').value,
            outlet_id: checkoutOutletSelect ? checkoutOutletSelect.value : '',
            payment_method: checkoutPaymentMethod ? checkoutPaymentMethod.value : 'qris',
            client_request_id: activeCheckoutRequestId,
            // Ubah format cart item menjadi format yang dimengerti server
            items: cart.map(item => ({
                pesanan_item: item.name,
                jumlah: item.qty,
                harga_satuan: item.price
            }))
        };

        try {
            // Kirim data pesanan ke server via POST
            const result = await apiRequest('/pesanan', 'POST', data);
            if (result.success) {
                const waUrl   = result.whatsapp_url || '';
                const orderCode = result.order_code || '';

                // ---- Fungsi untuk membersihkan state cart setelah selesai ----
                function resetAfterCheckout() {
                    clearCart();
                    cartModal.classList.add('hidden');
                    checkoutForm.reset();
                    cartCheckoutSection.classList.add('hidden');
                    cartActionButtons.classList.remove('hidden');
                    hasManualOutletSelection = false;
                    activeCheckoutRequestId = null;
                    setCheckoutSubmittingState(false);
                    if (checkoutOutletSearch) checkoutOutletSearch.value = '';
                    renderOutletOptions('', false);
                    if (checkoutPaymentHint) checkoutPaymentHint.textContent = getCheckoutPaymentHint('qris');
                }

                // ---- Fungsi untuk membuka dialog upload bukti ----
                function openUploadProofDialog() {
                    Swal.fire({
                        title: '<span style="font-size:1rem;font-weight:800;color:#1e293b;">Upload Bukti Pembayaran</span>',
                        html: `
                            <div style="text-align:left;font-family:'Poppins',sans-serif;">
                                <p style="font-size:13px;color:#64748b;margin-bottom:14px;">Foto bukti transfer/QRIS kamu (JPG, PNG, WEBP · maks. 5MB)</p>
                                <label for="proof-file-input"
                                    style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;
                                           border:2px dashed #cbd5e1;border-radius:16px;padding:28px 16px;cursor:pointer;
                                           background:#f8fafc;transition:border-color 0.2s,background 0.2s;"
                                    id="proof-drop-label"
                                    onmouseover="this.style.borderColor='#94a3b8';this.style.background='#f1f5f9';"
                                    onmouseout="this.style.borderColor='#cbd5e1';this.style.background='#f8fafc';">
                                    <svg id="proof-upload-icon" xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    <span id="proof-label-text" style="font-size:13px;color:#64748b;font-weight:600;">Klik atau seret gambar ke sini</span>
                                    <input type="file" id="proof-file-input" accept="image/*" style="display:none;">
                                </label>
                                <div id="proof-preview-wrap" style="display:none;margin-top:14px;text-align:center;">
                                    <img id="proof-preview-img" src="" alt="Preview"
                                        style="max-width:100%;max-height:220px;object-fit:contain;border-radius:14px;border:2px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,0.1);">
                                    <p id="proof-file-name" style="margin-top:8px;font-size:12px;color:#64748b;"></p>
                                </div>
                                <div id="proof-error" style="display:none;margin-top:10px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:13px;color:#dc2626;font-weight:600;"></div>
                                <div id="proof-progress-wrap" style="display:none;margin-top:14px;">
                                    <div style="height:6px;background:#e2e8f0;border-radius:99px;overflow:hidden;">
                                        <div id="proof-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#ef4444,#b91c1c);border-radius:99px;transition:width 0.3s ease;"></div>
                                    </div>
                                    <p style="text-align:center;font-size:12px;color:#64748b;margin-top:6px;">Mengupload bukti...</p>
                                </div>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Upload Sekarang',
                        confirmButtonColor: '#b91c1c',
                        showCancelButton: true,
                        cancelButtonText: 'Nanti Saja',
                        cancelButtonColor: '#64748b',
                        didOpen: () => {
                            const fileInput = document.getElementById('proof-file-input');
                            const previewWrap = document.getElementById('proof-preview-wrap');
                            const previewImg  = document.getElementById('proof-preview-img');
                            const fileName    = document.getElementById('proof-file-name');
                            const uploadIcon  = document.getElementById('proof-upload-icon');
                            const labelText   = document.getElementById('proof-label-text');
                            const errorEl     = document.getElementById('proof-error');

                            fileInput.addEventListener('change', () => {
                                const file = fileInput.files[0];
                                errorEl.style.display = 'none';
                                if (!file) { previewWrap.style.display = 'none'; return; }

                                // Validasi ukuran client-side
                                if (file.size > 5 * 1024 * 1024) {
                                    errorEl.textContent = 'Ukuran gambar terlalu besar. Maksimal 5MB.';
                                    errorEl.style.display = 'block';
                                    fileInput.value = '';
                                    previewWrap.style.display = 'none';
                                    return;
                                }

                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    previewImg.src = e.target.result;
                                    previewWrap.style.display = 'block';
                                    uploadIcon.style.opacity = '0.4';
                                    labelText.textContent = 'Ganti gambar';
                                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                                };
                                reader.readAsDataURL(file);
                            });
                        },
                        preConfirm: async () => {
                            const fileInput = document.getElementById('proof-file-input');
                            const errorEl   = document.getElementById('proof-error');
                            const progressWrap = document.getElementById('proof-progress-wrap');
                            const progressBar  = document.getElementById('proof-progress-bar');

                            if (!fileInput.files[0]) {
                                errorEl.textContent = 'Pilih file gambar terlebih dahulu.';
                                errorEl.style.display = 'block';
                                return false;
                            }

                            progressWrap.style.display = 'block';
                            Swal.getConfirmButton().disabled = true;
                            Swal.getCancelButton().disabled  = true;

                            // Simulate progress
                            let progress = 0;
                            const progressInterval = setInterval(() => {
                                progress = Math.min(progress + 15, 85);
                                progressBar.style.width = progress + '%';
                            }, 200);

                            try {
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                const formData = new FormData();
                                formData.append('payment_proof', fileInput.files[0]);

                                const response = await fetch(resolveAppUrl(`/pesanan/${encodeURIComponent(orderCode)}/upload-proof`), {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                    body: formData,
                                    credentials: 'same-origin',
                                });

                                clearInterval(progressInterval);
                                progressBar.style.width = '100%';

                                const result = await response.json();
                                if (!response.ok || !result.success) {
                                    const validationMessage = result.errors && result.errors.payment_proof
                                        ? result.errors.payment_proof[0]
                                        : '';

                                    errorEl.textContent = validationMessage || result.message || 'Upload gagal. Coba lagi.';
                                    errorEl.style.display = 'block';
                                    progressWrap.style.display = 'none';
                                    Swal.getConfirmButton().disabled = false;
                                    Swal.getCancelButton().disabled  = false;
                                    return false;
                                }
                                return result;
                            } catch (err) {
                                clearInterval(progressInterval);
                                errorEl.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
                                errorEl.style.display = 'block';
                                progressWrap.style.display = 'none';
                                Swal.getConfirmButton().disabled = false;
                                Swal.getCancelButton().disabled  = false;
                                return false;
                            }
                        },
                        allowOutsideClick: () => !Swal.isLoading(),
                    }).then((uploadResult) => {
                        if (uploadResult.isConfirmed && uploadResult.value) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bukti Terkirim!',
                                text: 'Bukti pembayaranmu berhasil diupload. Admin akan segera mengonfirmasi ya!',
                                confirmButtonColor: '#16a34a',
                                confirmButtonText: 'Tutup',
                            }).then(resetAfterCheckout);
                        } else {
                            resetAfterCheckout();
                        }
                    });
                }

                // ---- Popup utama sukses checkout ----
                Swal.fire({
                    icon: 'success',
                    title: 'Pesanan Tersimpan!',
                    html: buildPaymentDetailsHtml(result),
                    confirmButtonColor: '#D20000',
                    confirmButtonText: 'Upload Bukti Bayar',
                    showDenyButton:  !!waUrl,
                    denyButtonText:  'Hubungi Admin',
                    denyButtonColor: '#16a34a',
                    showCancelButton: true,
                    cancelButtonText: 'Nanti Saja',
                    cancelButtonColor: '#64748b',
                }).then((swalResult) => {
                    if (swalResult.isConfirmed) {
                        // Tombol "Upload Bukti Bayar"
                        openUploadProofDialog();
                    } else {
                        resetAfterCheckout();
                        if (swalResult.isDenied && waUrl) {
                            window.open(waUrl, '_blank');
                        }
                    }
                });
            } else {
                activeCheckoutRequestId = null;
                setCheckoutSubmittingState(false);
                Swal.fire({icon: 'error', title: 'Gagal Pesan', text: result.message || 'Waduh, pesananmu gagal gaes. Coba lagi yuk!', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            activeCheckoutRequestId = null;
            setCheckoutSubmittingState(false);
            Swal.fire({
                icon: 'error',
                title: 'Periksa Datanya!',
                text: (err && err.message) ? err.message : 'Wah, sepertinya ada data yang belum lengkap nih. Yuk cek lagi!',
                confirmButtonColor: '#D20000'
            });
        }
    });

    // ====================================================================
    // CEK STATUS LOGIN (AUTH)
    // ====================================================================

    /**
     * checkAuth() - Cek apakah user sudah login atau belum
     *
     * Mengirim request ke '/api/user' untuk cek session.
     * Jika sudah login → simpan data user ke currentUser
     * Jika belum → currentUser = null
     * Lalu update tampilan UI sesuai status login
     */
    async function checkAuth() {
        try {
            const result = await apiRequest('/api/user');
            if (result.logged_in) {
                currentUser = result.user;  // Simpan data user
                const backofficePath = getBackofficeDashboardPath(currentUser);
                if (backofficePath) {
                    window.location.href = resolveAppUrl(backofficePath);
                    return;
                }
            } else {
                currentUser = null;
            }
        } catch (e) {
            currentUser = null; // Jika error, anggap belum login
        }
        updateLoginUI(); // Update tampilan (icon, menu admin, dll)
    }

    // ====================================================================
    // FILTER KATEGORI & CAROUSEL MENU
    // ====================================================================

    /**
     * getFilteredProducts() - Dapatkan produk sesuai filter kategori aktif
     * @returns {Array} - Array produk yang sudah difilter
     */
    function getFilteredProducts() {
        if (currentCategory === 'semua') return products;
        return products.filter(p => (p.category || 'makanan') === currentCategory);
    }

    /**
     * getTotalPages(filtered) - Hitung jumlah halaman carousel
     * @param {Array} filtered - Array produk yang sudah difilter
     * @returns {number} - Jumlah halaman (dibulatkan ke atas)
     *
     * Math.ceil() membulatkan ke atas. Contoh: 5 item / 4 per page = 2 halaman
     */
    function getTotalPages(filtered) {
        return Math.ceil(filtered.length / ITEMS_PER_PAGE);
    }

    /**
     * updateCarouselControls(filtered) - Update tampilan kontrol carousel
     * @param {Array} filtered - Array produk yang sudah difilter
     *
     * Mengatur tampilan tombol prev/next dan dots berdasarkan:
     * - Apakah perlu carousel (lebih dari 1 halaman)
     * - Halaman mana yang sedang aktif
     * - Disable tombol jika di halaman pertama/terakhir
     */
    function updateCarouselControls(filtered) {
        const totalPages = getTotalPages(filtered);
        const needsCarousel = totalPages > 1; // Perlu carousel jika lebih dari 1 halaman

        if (carouselPrev && carouselNext) {
            if (needsCarousel) {
                // Tampilkan tombol prev dan next
                carouselPrev.classList.remove('hidden');
                carouselPrev.classList.add('flex');
                carouselNext.classList.remove('hidden');
                carouselNext.classList.add('flex');
                // Disable tombol jika di halaman pertama/terakhir
                carouselPrev.disabled = carouselPage === 0;
                carouselNext.disabled = carouselPage >= totalPages - 1;
                // Buat transparan jika disabled
                carouselPrev.style.opacity = carouselPage === 0 ? '0.3' : '1';
                carouselNext.style.opacity = carouselPage >= totalPages - 1 ? '0.3' : '1';
            } else {
                // Sembunyikan tombol jika tidak perlu carousel
                carouselPrev.classList.add('hidden');
                carouselPrev.classList.remove('flex');
                carouselNext.classList.add('hidden');
                carouselNext.classList.remove('flex');
            }
        }

        // Buat dots navigasi carousel
        if (carouselDots) {
            if (needsCarousel) {
                carouselDots.classList.remove('hidden');
                carouselDots.innerHTML = '';
                // Buat 1 dot untuk setiap halaman
                for (let i = 0; i < totalPages; i++) {
                    const dot = document.createElement('button');
                    // Dot aktif: warna merah & lebih besar. Yang lain: abu-abu
                    dot.className = `w-3 h-3 rounded-full transition-all duration-300 ${i === carouselPage ? 'bg-primary-red scale-125 shadow-md' : 'bg-gray-300 hover:bg-gray-400'}`;
                    // Klik dot → pindah ke halaman tersebut
                    dot.addEventListener('click', () => {
                        carouselPage = i;
                        renderProducts();
                    });
                    carouselDots.appendChild(dot);
                }
            } else {
                carouselDots.classList.add('hidden');
            }
        }
    }

    // ====================================================================
    // EVENT LISTENER TAB KATEGORI
    // ====================================================================
    // Saat tab kategori diklik (Semua/Makanan/Minuman):
    // 1. Set kategori aktif
    // 2. Reset carousel ke halaman pertama
    // 3. Update style tab (yang aktif = merah, yang lain = putih)
    // 4. Render ulang produk sesuai filter baru

    const categoryTabs = document.querySelectorAll('.category-tab');
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            currentCategory = tab.dataset.category; // Ambil kategori dari data-category
            carouselPage = 0;                        // Reset ke halaman pertama

            // Hapus style aktif dari semua tab
            categoryTabs.forEach(t => {
                t.classList.remove('bg-primary-red', 'text-white', 'active-tab');
                t.classList.add('bg-white', 'text-text-dark', 'border', 'border-gray-200');
            });
            // Berikan style aktif ke tab yang diklik
            tab.classList.add('bg-primary-red', 'text-white', 'active-tab');
            tab.classList.remove('bg-white', 'text-text-dark', 'border', 'border-gray-200');

            renderProducts(); // Render ulang produk
        });
    });

    // ====================================================================
    // NAVIGASI CAROUSEL (Tombol Prev/Next)
    // ====================================================================

    // Tombol "Sebelumnya" (←)
    if (carouselPrev) {
        carouselPrev.addEventListener('click', () => {
            const filtered = getFilteredProducts();
            if (carouselPage > 0) {
                carouselPage--;      // Mundur 1 halaman
                renderProducts();
            }
        });
    }
    // Tombol "Berikutnya" (→)
    if (carouselNext) {
        carouselNext.addEventListener('click', () => {
            const filtered = getFilteredProducts();
            const totalPages = getTotalPages(filtered);
            if (carouselPage < totalPages - 1) {
                carouselPage++;      // Maju 1 halaman
                renderProducts();
            }
        });
    }

    // ====================================================================
    // RENDER PRODUK KE HALAMAN (Fungsi Utama Tampilan Menu)
    // ====================================================================

    /**
     * renderProducts() - Render/tampilkan produk ke dalam grid menu
     *
     * Ini adalah fungsi UTAMA yang menampilkan card produk di halaman.
     * Dipanggil setiap kali ada perubahan: filter kategori, pindah halaman,
     * login/logout, tambah/hapus produk, dll.
     *
     * Fitur:
     * - Mendukung 2 mode tampilan: Grid (kotak) dan List (daftar)
     * - Menampilkan badge ("BEST SELLER", dll), rating, kategori
     * - Tombol admin (edit/hapus) hanya muncul untuk admin
     * - Animasi card muncul bertahap (staggered animation)
     */
    function renderProducts() {
        menuGrid.innerHTML = '';   // Kosongkan grid terlebih dahulu

        // Cek apakah user adalah admin (untuk menampilkan tombol edit/hapus)
        const isAdmin = currentUser && currentUser.role === 'admin';

        // Ambil mode tampilan dari localStorage (default: 'grid')
        const layoutMode = localStorage.getItem('menuLayout') || 'grid';

        const filtered = getFilteredProducts();
        const totalPages = getTotalPages(filtered);

        // Pastikan halaman carousel tidak melebihi jumlah halaman yang tersedia
        if (carouselPage >= totalPages) carouselPage = Math.max(0, totalPages - 1);

        // Ambil item untuk halaman saat ini (4 item per halaman)
        const start = carouselPage * ITEMS_PER_PAGE;
        const pageItems = filtered.slice(start, start + ITEMS_PER_PAGE);

        // Set class grid sesuai mode tampilan
        if (layoutMode === 'grid') {
            // Mode Grid: 1 kolom di mobile, 2 di tablet, 4 di desktop
            menuGrid.className = "menu-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 transition-all duration-500 ease-in-out";
        } else {
            // Mode List: 1 kolom vertikal
            menuGrid.className = "menu-grid flex flex-col gap-6 max-w-4xl mx-auto transition-all duration-500 ease-in-out";
        }

        // Jika tidak ada produk di halaman ini
        if (pageItems.length === 0) {
            menuGrid.innerHTML = `
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-utensils text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-400 text-lg">Belum ada menu di kategori ini.</p>
                </div>`;
            updateCarouselControls(filtered);
            return;
        }

        // Loop setiap produk dan buat card HTML-nya
        pageItems.forEach((product, index) => {
            const card = document.createElement('div');
            card.dataset.id = product.id; // Simpan ID produk di data attribute

            // ============================================================
            // RATING BINTANG - Hitung dan tampilkan rating dari ulasan
            // ============================================================
            let ratingHtml = '';
            let avgRating = 0;
            let totalReviews = 0;

            if (product.reviews && product.reviews.length > 0) {
                totalReviews = product.reviews.length;
                // Hitung total rating
                const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating), 0);
                // Hitung rata-rata (1 desimal)
                avgRating = (sum / totalReviews).toFixed(1);

                // Buat HTML bintang
                let stars = '';
                const fullStars = Math.floor(avgRating);          // Bintang penuh
                const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;   // Setengah bintang
                for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star text-yellow-500"></i>';
                if (halfStar) stars += '<i class="fas fa-star-half-alt text-yellow-500"></i>';

                // HTML rating: bintang + angka + jumlah ulasan (bisa diklik untuk buka ulasan)
                ratingHtml = `
                    <div class="rating flex justify-center items-center gap-1 text-sm mt-2 cursor-pointer hover:bg-gray-50 rounded px-2 py-1 transition" onclick="openReviewModal(${product.id})">
                        <div class="flex text-yellow-500">${stars}</div>
                        <span class="font-bold text-text-dark ml-1">${avgRating}</span>
                        <span class="text-gray-400 text-xs">(${totalReviews})</span>
                    </div>`;
            } else {
                // Belum ada ulasan - tampilkan link untuk beri ulasan
                ratingHtml = `
                    <div class="rating flex justify-center items-center gap-1 text-sm mt-2 cursor-pointer text-gray-400 hover:text-yellow-600 transition" onclick="openReviewModal(${product.id})">
                        <i class="far fa-star"></i> <span class="text-xs">Beri Ulasan</span>
                    </div>`;
            }

            // ============================================================
            // TOMBOL ADMIN - Edit & Hapus (hanya muncul untuk admin)
            // ============================================================
            let adminControls = '';
            if (isAdmin) {
                // Tombol muncul saat hover di card (opacity-0 → opacity-100)
                adminControls = `
                    <div class="absolute top-2 right-2 flex gap-2 z-10 transition-opacity opacity-0 group-hover:opacity-100">
                        <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-yellow-600 transition shadow-sm border border-gray-200" onclick="editProduct(${product.id})" title="Edit"><i class="fas fa-pencil-alt text-xs"></i></button>
                        <button class="bg-white/80 text-gray-700 p-2 rounded-full hover:bg-white hover:text-red-600 transition shadow-sm border border-gray-200" onclick="deleteProduct(${product.id})" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                    </div>`;
            }

            // ============================================================
            // BADGE - Label khusus seperti "BEST SELLER"
            // ============================================================
            let badgeHtml = '';
            if (product.badge) {
                badgeHtml = `<div class="badge absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`;
            }

            // ============================================================
            // KATEGORI - Icon dan label (Makanan/Minuman)
            // ============================================================
            const categoryIcon = (product.category || 'makanan') === 'minuman' ? 'fa-glass-water' : 'fa-drumstick-bite';
            const categoryLabel = (product.category || 'makanan') === 'minuman' ? 'Minuman' : 'Makanan';

            // Path gambar: URL eksternal (http) atau path lokal
            const imgSrc = resolveAssetUrl(product.image);

            // ============================================================
            // TAMPILAN CARD: MODE GRID (Kotak/Card)
            // ============================================================
            if (layoutMode === 'grid') {
                card.className = "product-card bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 mt-0 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100";
                const imgContainer = `
                    <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                        ${badgeHtml}
                        <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
                    </div>`;

                card.innerHTML = `
                    ${adminControls}
                    ${imgContainer}
                    <div class="product-info flex flex-col flex-grow">
                        <div class="flex justify-center mb-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${(product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'}">
                                <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                            </span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc}</p>
                        ${ratingHtml}
                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-50">
                            <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                            <button class="btn-cart w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm" title="Tambah ke Keranjang">
                                <i class="fas fa-cart-plus text-sm"></i>
                            </button>
                        </div>
                    </div>`;
            } else {
                // ============================================================
                // TAMPILAN CARD: MODE LIST (Daftar Horizontal)
                // ============================================================
                card.className = "product-card bg-white rounded-2xl p-4 shadow-md transition-all duration-300 relative group flex flex-col md:flex-row items-center gap-6 hover:shadow-lg border border-gray-100";
                if (product.badge) {
                    badgeHtml = `<div class="badge absolute top-3 left-3 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10">${product.badge}</div>`;
                }
                card.innerHTML = `
                    ${badgeHtml}
                    ${adminControls}
                    <div class="img-container shrink-0 w-full md:w-[160px] aspect-square bg-gray-50 rounded-xl flex items-center justify-center p-2">
                        <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <div class="product-info flex-grow text-center md:text-left w-full">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start h-full">
                            <div class="flex flex-col justify-between h-full">
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-2 ${(product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600'}">
                                        <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                                    </span>
                                    <h3 class="font-bold text-xl text-gray-800 mb-2">${product.name}</h3>
                                    <p class="text-gray-500 text-sm mb-3 max-w-lg">${product.desc}</p>
                                </div>
                                <div class="flex justify-center md:justify-start">${ratingHtml}</div>
                            </div>
                            <div class="price-action flex flex-row md:flex-col items-center justify-between md:justify-center md:items-end gap-3 mt-4 md:mt-0 w-full md:w-auto">
                                <span class="font-extrabold text-2xl text-red-600">${formatRupiah(product.price)}</span>
                                <button class="btn-cart bg-red-600 text-white w-full md:w-auto px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center justify-center gap-2" title="Tambah ke Keranjang">
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                </button>
                            </div>
                        </div>
                    </div>`;
            }

            // ============================================================
            // ANIMASI CARD MUNCUL (Staggered Animation)
            // ============================================================
            // Card awalnya tak terlihat (opacity 0) & sedikit ke bawah
            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";

            // Setelah delay bertahap (80ms × index), card muncul
            // cubic-bezier(...) = kurva animasi yang memberi efek "bouncy"
            setTimeout(() => {
                card.style.transition = "all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
            }, index * 80);

            menuGrid.appendChild(card); // Tambahkan card ke grid
        });

        // Pasang event listener pada semua tombol "Tambah ke Keranjang"
        document.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', handleCartClick);
        });

        // Sinkronkan radio button Layout Mode dengan setting yang tersimpan
        const radios = document.querySelectorAll('input[name="layout_mode"]');
        const currentLayoutMode = localStorage.getItem('menuLayout') || 'grid';
        radios.forEach(r => { if (r.value === currentLayoutMode) r.checked = true; });

        // Update kontrol carousel (tombol prev/next dan dots)
        updateCarouselControls(filtered);
    }

    // ====================================================================
    // UPDATE TAMPILAN UI LOGIN/LOGOUT
    // ====================================================================

    /**
     * updateLoginUI() - Update tampilan UI berdasarkan status login
     *
     * Jika sudah login:
     * - Icon berubah dari fa-sign-in-alt → fa-user (icon orang)
     * - Menu admin muncul jika role = admin
     * - FAB (tombol tambah menu) muncul jika admin
     *
     * Jika belum login:
     * - Icon kembali ke fa-sign-in-alt
     * - Menu admin dan FAB disembunyikan
     */
    function updateLoginUI() {
        const loginIcon = btnLoginHeader.querySelector('i');
        const fab = document.getElementById('btn-add-menu-fab');
        const cartIcon = btnCartHeader.querySelector('i');
        const cartBadgeEl = document.getElementById('cart-badge');

        // RESET: Hapus semua class icon terlebih dahulu
        const isAdmin = currentUser && currentUser.role === 'admin';

        // Ambil referensi tab slider sekali di luar blok if/else
        const tabSlider = document.getElementById('settings-tab-slider');

        if (currentUser) {
            // USER SUDAH LOGIN
            loginIcon.className = 'fas fa-user';
            btnLoginHeader.title = `${currentUser.name} (Logout)`;

            if (isAdmin) {
                // === ADMIN ===
                navAdmin.classList.remove('hidden');
                if (fab) fab.classList.remove('hidden');
                // Icon → clipboard-check (pesanan)
                cartIcon.className = 'fas fa-clipboard-check';
                btnCartHeader.title = 'Lihat Pesanan Masuk';
                
                // Tampilkan tab switcher di settings
                if(tabSlider) tabSlider.classList.remove('hidden');
            } else {
                // === USER BIASA (PELANGGAN) ===
                navAdmin.classList.add('hidden');
                if (fab) fab.classList.add('hidden');
                // Icon → shopping-cart (keranjang)
                cartIcon.className = 'fas fa-shopping-cart';
                btnCartHeader.title = 'Keranjang Belanja';
                if (document.getElementById('btn-show-user-orders')) document.getElementById('btn-show-user-orders').classList.remove('hidden');
                if (document.getElementById('btn-show-user-orders-nav')) document.getElementById('btn-show-user-orders-nav').classList.remove('hidden');
                if (document.getElementById('btn-show-user-orders-nav')) document.getElementById('btn-show-user-orders-nav').classList.add('flex');
                
                // Sembunyikan tab switcher di settings
                if(tabSlider) tabSlider.classList.add('hidden');
            }
        } else {
            // BELUM LOGIN
            loginIcon.className = 'fas fa-sign-in-alt';
            btnLoginHeader.title = "Login";
            navAdmin.classList.add('hidden');
            if (fab) fab.classList.add('hidden');
            // Icon → shopping-cart (keranjang)
            cartIcon.className = 'fas fa-shopping-cart';
            btnCartHeader.title = 'Keranjang Belanja';
            if (document.getElementById('btn-show-user-orders')) document.getElementById('btn-show-user-orders').classList.add('hidden');
            if (document.getElementById('btn-show-user-orders-nav')) document.getElementById('btn-show-user-orders-nav').classList.add('hidden');
            if (document.getElementById('btn-show-user-orders-nav')) document.getElementById('btn-show-user-orders-nav').classList.remove('flex');
            
            // Sembunyikan tab switcher di settings
            if(tabSlider) tabSlider.classList.add('hidden');

            // PENTING: Hentikan polling & sembunyikan badge saat belum login
            stopOrderPolling();
            // Pastikan badge benar-benar tersembunyi
            if (cartBadgeEl) {
                cartBadgeEl.classList.add('hidden');
                cartBadgeEl.classList.remove('animate-bounce');
                cartBadgeEl.textContent = '0';
            }
        }

        updateMobileDrawerUI();
        renderProducts();

        // Mulai/hentikan polling notifikasi pesanan (SATU TEMPAT SAJA)
        if (isAdmin) {
            startOrderPolling();
        } else {
            stopOrderPolling();
        }
    }

    // ====================================================================
    // PROSES LOGIN
    // ====================================================================

    /**
     * Saat form login di-submit:
     * 1. Ambil username & password dari input
     * 2. Kirim ke server via AJAX POST ke '/login'
     * 3. Jika berhasil → simpan user, update UI, tutup modal
     * 4. Jika gagal → tampilkan alert error
     */
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Cegah halaman reload
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            // Gunakan silent: true agar jika CSRF expired, tidak tampil pop-up menakutkan
            // Kita akan handle sendiri dengan pesan yang lebih ramah
            const result = await apiRequest('/login', 'POST', { username, password }, { silent: true });
            if (result.success) {
                // Update CSRF Token dari response (karena session diregenerate)
                if (result.csrf_token) {
                    const m = document.querySelector('meta[name="csrf-token"]');
                    if(m) m.setAttribute('content', result.csrf_token);
                    if(typeof CSRF_TOKEN !== 'undefined') CSRF_TOKEN = result.csrf_token;
                }
                currentUser = result.user;        // Simpan data user
                Swal.fire({icon: 'success', title: 'Yeay Berhasil!', text: result.message || 'Selamat datang di dunia penuh kelezatan, Chi-Pok!', confirmButtonColor: '#D20000'}).then(() => {
                    // Jika admin → redirect ke halaman dashboard admin terpisah
                    const backofficePath = getBackofficeDashboardPath(currentUser);
                    if (backofficePath) {
                        window.location.href = resolveAppUrl(backofficePath);
                        return;
                    }
                    updateLoginUI();                   // Update tampilan
                    loginModal.classList.add('hidden'); // Tutup modal login
                    loginForm.reset();                 // Kosongkan form
                });
            } else {
                Swal.fire({icon: 'error', title: 'Oops!', text: result.message || 'Username atau password salah!', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            if (err.message === 'CSRF token expired') {
                // CSRF token basi (biasanya karena halaman dibuka lama atau server restart)
                // Reload halaman otomatis untuk mendapatkan token baru
                Swal.fire({
                    icon: 'info',
                    title: 'Memperbarui Halaman...',
                    text: 'Halaman perlu dimuat ulang sebentar. Silakan login kembali ya!',
                    confirmButtonColor: '#D20000',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }

            const message = (err && err.message) ? String(err.message) : '';
            const isNetworkError =
                err instanceof TypeError ||
                /failed to fetch|networkerror|network error|fetch/i.test(message);

            Swal.fire({
                icon: 'error',
                title: isNetworkError ? 'Koneksi Bermasalah!' : 'Terjadi Kesalahan!',
                text: isNetworkError
                    ? 'Gagal terhubung ke server. Pastikan server Laravel berjalan dan kamu membuka website dari server tersebut.'
                    : (message || 'Terjadi kesalahan saat login. Silakan coba lagi.'),
                confirmButtonColor: '#D20000'
            });
        }
    });

    // ====================================================================
    // PROSES REGISTRASI (DAFTAR AKUN BARU)
    // ====================================================================

    /**
     * Saat form signup di-submit:
     * 1. Ambil semua data dari input (email, username, no WA, password)
     * 2. Kirim ke server via AJAX POST ke '/register'
     * 3. Jika berhasil → tutup modal signup, buka modal login
     */
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('signup_email').value;
        const username = document.getElementById('signup_username').value;
        const no_hp = document.getElementById('signup_no_hp').value;
        const password = document.getElementById('signup_password').value;

        try {
            const result = await apiRequest('/register', 'POST', { email, username, no_hp, password }, { silent: true });
            if (result.success) {
                Swal.fire({icon: 'success', title: 'Pendaftaran Sukses!', text: result.message || 'Yeay! Akun barumu sudah siap. Yuk, langsung login!', confirmButtonColor: '#D20000'}).then(() => {
                    signupModal.classList.add('hidden');   // Tutup modal signup
                    loginModal.classList.remove('hidden'); // Buka modal login
                    signupForm.reset();
                });
            } else {
                Swal.fire({icon: 'error', title: 'Yah Gagal!', text: result.message || 'Aduh, pendaftaran gagal. Pastikan semua datamu unik ya.', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            if (err.message === 'CSRF token expired') {
                Swal.fire({
                    icon: 'info',
                    title: 'Memperbarui Halaman...',
                    text: 'Halaman perlu dimuat ulang sebentar. Silakan coba daftar lagi ya!',
                    confirmButtonColor: '#D20000',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }
            Swal.fire({icon: 'error', title: 'Aduh..', text: 'Terjadi kesalahan sistem saat mendaftar. Sabar ya, coba lagi nanti.', confirmButtonColor: '#D20000'});
        }
    });

    // ====================================================================
    // PROSES LOGOUT
    // ====================================================================

    /**
     * Saat tombol login/logout di header diklik:
     * - Jika SUDAH login → tampilkan konfirmasi, lalu logout
     * - Jika BELUM login → buka modal login
     */
    btnLoginHeader.addEventListener('click', async () => {
        if (currentUser) {
            Swal.fire({
                title: 'Mau Pergi?',
                text: "Yakin ingin keluar dari akun Chi-Pok kamu?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#D20000',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // PENTING: Hentikan polling DAN null-kan user SEBELUM kirim logout
                    // Ini mencegah race condition: polling kirim request → server sudah
                    // invalidasi session → polling dapat 419 → muncul pop-up error
                    stopOrderPolling();
                    currentUser = null;

                    try {
                        // Gunakan silent: true karena kita intentional logout
                        // Jika session sudah expired (419), tidak perlu pop-up error
                        const res = await apiRequest('/logout', 'POST', null, { silent: true });
                        if (res.csrf_token) {
                            const m = document.querySelector('meta[name="csrf-token"]');
                            if(m) m.setAttribute('content', res.csrf_token);
                            if(typeof CSRF_TOKEN !== 'undefined') CSRF_TOKEN = res.csrf_token;
                        }
                    } catch(e) {
                        // Jika logout gagal (misal session sudah expired), tidak masalah
                        // User tetap di-logout di sisi client
                    }
                    Swal.fire({icon: 'success', title: 'Sampai Jumpa!', text: 'Kamu berhasil logout. Ditunggu kedatangannya lagi ya!', confirmButtonColor: '#D20000'});
                    updateLoginUI();
                }
            });
        } else {
            // User belum login → buka modal login
            loginModal.classList.remove('hidden');
        }
    });

    // ====================================================================
    // PANEL ADMIN - Kelola Pesanan
    // ====================================================================

    // Saat tombol "ADMIN" di navbar diklik → buka panel admin
    btnAdminPanel.addEventListener('click', (e) => {
        e.preventDefault();                          // Cegah navigasi default
        adminModal.classList.remove('hidden');        // Tampilkan modal admin
        renderOrdersTable();                          // Muat data pesanan
    });

    // ====================================================================
    // FUNGSI KELOLA BANNER
    // ====================================================================
    // Fungsi muat banner
    async function renderAdminBanners() {
        const container = document.getElementById('banner-list-container');
        if (!container) return;
        container.innerHTML = '<div class="col-span-full text-center text-sm text-gray-500">Memuat banner...</div>';

        try {
            const res = await apiRequest('/admin/banners');
            if (res.success && res.data.length > 0) {
                container.innerHTML = '';
                res.data.forEach(banner => {
                    const card = document.createElement('div');
                    card.className = 'relative bg-white border rounded shadow-sm overflow-hidden group';
                    card.innerHTML = `
                        <img src="${resolveAssetUrl(banner.image_path)}" class="w-full h-24 object-cover" alt="Banner">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center text-white p-2 text-center">
                            <span class="text-xs mb-2 font-medium truncate w-full">${banner.description || 'Tidak ada deskripsi'}</span>
                            <button onclick="window.deleteBanner(${banner.id})" class="bg-red-600 hover:bg-red-700 text-white rounded-full p-2 text-xs transition">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    `;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<div class="col-span-full text-center text-sm text-gray-400 py-4"><i class="fas fa-images text-2xl mb-2 opacity-50 block"></i>Belum ada banner terpasang.</div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="col-span-full text-center text-sm text-red-500 py-4">Gagal memuat banner.</div>';
        }
    }

    // Fungsi submit banner
    const formAddBanner = document.getElementById('form-add-banner');
    if (formAddBanner) {
        formAddBanner.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formAddBanner);
            
            const submitBtn = formAddBanner.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah...';
            submitBtn.disabled = true;

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const token = csrfMeta ? csrfMeta.getAttribute('content') : CSRF_TOKEN;

                const response = await fetch(resolveAppUrl('/admin/banners'), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });

                // Handle sesi kedaluwarsa (419) - sama seperti apiRequest
                if (response.status === 419) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Habis',
                        text: 'Kamu terlalu lama di halaman ini tanpa aktivitas. Halaman akan dimuat ulang, silakan coba lagi.',
                        confirmButtonColor: '#D20000',
                        confirmButtonText: 'Muat Ulang'
                    }).then(() => {
                        window.location.reload();
                    });
                    throw new Error('CSRF token expired');
                }

                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                        icon: 'success', title: 'Banner ditambahkan!'
                    });
                    formAddBanner.reset();
                    renderAdminBanners(); // refresh list
                } else {
                    Swal.fire({icon: 'error', title: 'Upload Gagal!', text: result.message || 'Pastikan file gambar tidak lebih dari 5MB dan formatnya JPG/PNG.', confirmButtonColor: '#D20000'});
                }
            } catch (err) {
                if (err.message === 'CSRF token expired') return;
                Swal.fire({icon: 'error', title: 'Koneksi Terputus', text: 'Tidak bisa mengunggah banner. Periksa koneksi internet kamu, lalu coba lagi.', confirmButtonColor: '#D20000'});
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // Fungsi global delete banner
    window.deleteBanner = async (id) => {
        Swal.fire({
            title: 'Hapus Banner?',
            text: "Banner ini akan dihapus dari sistem secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D20000',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await apiRequest('/admin/banners/' + id, 'DELETE');
                    if(res.success) {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                            icon: 'success', title: 'Banner dihapus!'
                        });
                        renderAdminBanners();
                    }
                } catch (e) {
                    Swal.fire({icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus banner.'});
                }
            }
        });
    };

    /**
     * renderOrdersTable() - Muat dan tampilkan data pesanan di tabel admin
     *
     * Mengambil data pesanan dari server ('/admin/api/pesanan')
     * dan menampilkannya dalam bentuk tabel HTML
     */
    async function renderOrdersTable() {
        const listContainer = document.getElementById('orders-table-body');
        if (!listContainer) return;
        listContainer.innerHTML = '<div class="col-span-full p-6 text-center text-sm text-gray-500">Memuat data pesanan...</div>';

        try {
            const result = await apiRequest('/admin/api/pesanan');
            if (result.success && result.data.length > 0) {
                listContainer.innerHTML = '';
                result.data.forEach(order => {
                    const card = document.createElement('div');
                    card.className = 'bg-white border text-left rounded-xl p-4 shadow-sm relative transition hover:shadow-md flex flex-col gap-3';
                    const orderType = normalizeOrderType(order.jenis);

                    // Info Pengguna & Kontak & Label Jenis
                    card.innerHTML = `
                        <div class="flex justify-between items-start border-b pb-2">
                            <div>
                                <h4 class="font-bold text-gray-900 leading-tight">${order.customerName}</h4>
                                <p class="text-xs text-gray-500 mb-1"><i class="fas fa-phone-alt opacity-75"></i> ${order.no_hp || '-'}</p>
                                <span class="px-2 py-0.5 inline-flex text-[10px] font-bold rounded-md ${orderType === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'} uppercase">
                                    ${order.jenis || '-'}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 font-medium mb-1"><i class="far fa-clock"></i> ${order.date}</p>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="px-2 py-1 inline-flex text-[10px] font-bold rounded-full ${order.payment_status === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}">
                                        ${order.payment_method_label || formatPaymentMethodLabel(order.payment_method)} • ${order.payment_status || '-'}
                                    </span>
                                    <span class="px-2 py-1 inline-flex text-[10px] sm:text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">
                                        ${order.status}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Pesanan & Alamat -->
                        <div class="flex-grow">
                            <p class="text-sm text-gray-700 font-medium line-clamp-2 leading-snug"><i class="fas fa-utensils text-red-400 mr-1.5"></i> ${order.items}</p>
                            ${orderType === 'delivery' ? `<p class="text-xs text-gray-500 mt-2 truncate max-w-full" title="${order.alamat || '-'}"><i class="fas fa-map-marker-alt text-gray-400 mr-1.5"></i> ${order.alamat || '-'}</p>` : ''}
                        </div>

                        <!-- Footer: Total Harga & Tombol Aksi -->
                        <div class="mt-2 pt-3 border-t flex justify-between items-center bg-gray-50/50 rounded-lg p-2">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total</span>
                                <span class="font-black text-gray-900">${formatRupiah(order.total)}</span>
                            </div>
                            <div>
                                ${getActionHtml(order.group_id, order.jenis, order.status)}
                            </div>
                        </div>
                    `;
                    listContainer.appendChild(card);
                });
            } else {
                listContainer.innerHTML = '<div class="col-span-full py-10 flex flex-col items-center justify-center text-center text-sm text-gray-500"><i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>Belum ada pesanan masuk.</div>';
            }
        } catch (err) {
            listContainer.innerHTML = '<div class="col-span-full p-4 text-center text-sm text-red-500">Gagal memuat data pesanan.</div>';
        }
    }

    /**
     * Helper action buttons untuk status
     */
    function getActionHtml(groupId, type, currentStatus) {
        let actionStatus = '';
        let btnText = '';
        let btnClass = '';
        type = (type || '').toLowerCase();

        if (currentStatus === 'Sedang Disiapkan') {
            if (type === 'delivery') {
                actionStatus = 'Sedang Diantar';
                btnText = 'Kirim Pesanan';
            } else {
                actionStatus = 'Pesanan Siap';
                btnText = 'Tandai Siap';
            }
            btnClass = 'bg-blue-600 hover:bg-blue-700';
        } else if (currentStatus === 'Sedang Diantar' || currentStatus === 'Pesanan Siap') {
            actionStatus = 'Selesai';
            btnText = 'Selesai';
            btnClass = 'bg-green-600 hover:bg-green-700';
        } else if (currentStatus === 'Selesai') {
            return `<span class="text-green-500 font-bold"><i class="fas fa-check-circle"></i> Selesai</span>`;
        }

        return `<button onclick="updateOrderStatus('${groupId}', '${actionStatus}')" class="px-3 py-1 rounded text-white text-xs font-bold transition-colors ${btnClass}">${btnText}</button>`;
    }

    // Expose updateOrderStatus to window
    window.updateOrderStatus = async function(groupId, newStatus) {
        try {
            const result = await apiRequest('/admin/api/pesanan/status', 'PUT', {
                group_id: groupId,
                status: newStatus
            });
            if (result.success) {
                renderOrdersTable(); // Refresh tabel
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    icon: 'success',
                    title: 'Status berhasil diubah'
                });
            }
        } catch (err) {
            Swal.fire({icon: 'error', title: 'Gagal', text: 'Tidak dapat mengubah status.', confirmButtonColor: '#D20000'});
        }
    };

    // ====================================================================
    // NOTIFIKASI PESANAN BARU (Admin Only)
    // ====================================================================

    let lastOrderCount = 0;          // Jumlah pesanan terakhir yang diketahui
    let orderPollingInterval = null;  // Interval polling
    let isFirstCheck = true;          // Flag untuk cek pertama (jangan bunyi saat pertama load)

    /**
     * playNotificationSound() - Mainkan suara notifikasi menggunakan Web Audio API
     * Menghasilkan suara "ding-dong" tanpa perlu file audio eksternal
     */
    function playNotificationSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

            // Nada pertama (ding) - frekuensi tinggi
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(830, audioCtx.currentTime);      // Nada E5
            gain1.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start(audioCtx.currentTime);
            osc1.stop(audioCtx.currentTime + 0.4);

            // Nada kedua (dong) - frekuensi lebih tinggi, delay sedikit
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1046, audioCtx.currentTime + 0.15); // Nada C6
            gain2.gain.setValueAtTime(0, audioCtx.currentTime);
            gain2.gain.setValueAtTime(0.3, audioCtx.currentTime + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.6);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(audioCtx.currentTime + 0.15);
            osc2.stop(audioCtx.currentTime + 0.6);

            // Nada ketiga (bonus sparkle)
            const osc3 = audioCtx.createOscillator();
            const gain3 = audioCtx.createGain();
            osc3.type = 'sine';
            osc3.frequency.setValueAtTime(1318, audioCtx.currentTime + 0.3); // Nada E6
            gain3.gain.setValueAtTime(0, audioCtx.currentTime);
            gain3.gain.setValueAtTime(0.2, audioCtx.currentTime + 0.3);
            gain3.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.8);
            osc3.connect(gain3);
            gain3.connect(audioCtx.destination);
            osc3.start(audioCtx.currentTime + 0.3);
            osc3.stop(audioCtx.currentTime + 0.8);
        } catch (e) {
            // Audio tidak didukung, abaikan
        }
    }

    /**
     * updateOrderBadge(count) - Tampilkan/sembunyikan badge notifikasi di icon pesanan
     */
    function updateOrderBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
            badge.classList.add('animate-bounce');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('animate-bounce');
        }
    }

    /**
     * checkNewOrders() - Cek apakah ada pesanan baru
     * Dijalankan secara berkala (polling) setiap 15 detik
     */
    async function checkNewOrders() {
        if (!currentUser || currentUser.role !== 'admin') return;

        try {
            // SILENT MODE: polling di background tidak boleh tampilkan pop-up error
            // Jika session expired/server restart, polling cukup diam saja
            const result = await apiRequest('/admin/api/pesanan', 'GET', null, { silent: true });

            // CEK ULANG setelah await — user mungkin sudah logout saat request berlangsung
            if (!currentUser || currentUser.role !== 'admin') return;

            if (result.success) {
                const currentCount = result.data.length;

                if (isFirstCheck) {
                    // Pertama kali: simpan jumlah tanpa bunyi notif
                    lastOrderCount = currentCount;
                    isFirstCheck = false;
                    updateOrderBadge(currentCount);
                    return;
                }

                if (currentCount > lastOrderCount) {
                    // Ada pesanan baru!
                    const newOrders = currentCount - lastOrderCount;

                    // Mainkan suara notifikasi
                    playNotificationSound();

                    // Tampilkan toast notifikasi
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        icon: 'info',
                        title: `🔔 ${newOrders} Pesanan Baru!`,
                        text: 'Klik icon pesanan untuk melihat detail.',
                        background: '#FEF3C7',
                        color: '#92400E',
                        didOpen: (toast) => {
                            toast.addEventListener('click', () => {
                                adminModal.classList.remove('hidden');
                                renderOrdersTable();
                                Swal.close();
                            });
                        }
                    });

                    lastOrderCount = currentCount;
                }

                // Update badge
                updateOrderBadge(currentCount);
            }
        } catch (e) {
            // Gagal cek, abaikan (coba lagi di polling berikutnya)
        }
    }

    /**
     * startOrderPolling() - Mulai polling cek pesanan baru setiap 15 detik
     */
    function startOrderPolling() {
        if (orderPollingInterval) clearInterval(orderPollingInterval);
        isFirstCheck = true;
        checkNewOrders(); // Cek langsung pertama kali
        orderPollingInterval = setInterval(checkNewOrders, 15000); // Cek setiap 15 detik
    }

    /**
     * stopOrderPolling() - Berhenti polling (saat logout atau bukan admin)
     */
    function stopOrderPolling() {
        if (orderPollingInterval) {
            clearInterval(orderPollingInterval);
            orderPollingInterval = null;
        }
        lastOrderCount = 0;
        isFirstCheck = true;
        updateOrderBadge(0);
    }

    // ====================================================================
    // PENGATURAN USER ORDERS MODAL (Pesanan Saya)
    // ====================================================================
    const userOrdersModal = document.getElementById('userOrdersModal');
    const btnShowUserOrders = document.getElementById('btn-show-user-orders'); // Tombol di dalam Cart
    const btnShowUserOrdersNav = document.getElementById('btn-show-user-orders-nav'); // Tombol di Header Navigasi
    const closeUserOrdersModal = document.getElementById('closeUserOrdersModal');

    // Event saat tombol "Pesanan Aktif" (dari dalam modal cart) diklik
    if (btnShowUserOrders) {
        btnShowUserOrders.addEventListener('click', () => {
            cartModal.classList.add('hidden'); // Sembunyikan cart
            userOrdersModal.classList.remove('hidden'); // Tampilkan pesanan
            renderUserOrdersTable(); // Load data pesanan
        });
    }

    // Event saat tombol "Pesanan Saya" di Header Navbar diklik
    if (btnShowUserOrdersNav) {
        btnShowUserOrdersNav.addEventListener('click', () => {
            userOrdersModal.classList.remove('hidden'); // Tampilkan pesanan
            renderUserOrdersTable(); // Load data pesanan
        });
    }

    if (closeUserOrdersModal) {
        closeUserOrdersModal.addEventListener('click', () => {
            userOrdersModal.classList.add('hidden');
        });
    }

    function getUserOrderStatusMeta(status) {
        const normalizedStatus = String(status || '').trim();

        if (normalizedStatus === 'Menunggu Pembayaran') {
            return {
                icon: 'fa-hourglass-half',
                label: 'Menunggu Pembayaran',
                badgeClass: 'bg-amber-50 text-amber-700 border border-amber-200'
            };
        }

        if (normalizedStatus === 'Diproses' || normalizedStatus === 'Sedang Disiapkan') {
            return {
                icon: 'fa-fire-burner',
                label: 'Sedang Diproses',
                badgeClass: 'bg-yellow-50 text-yellow-700 border border-yellow-200'
            };
        }

        if (normalizedStatus === 'Sedang Diantar') {
            return {
                icon: 'fa-motorcycle',
                label: 'Sedang Diantar',
                badgeClass: 'bg-blue-50 text-blue-700 border border-blue-200'
            };
        }

        if (normalizedStatus === 'Pesanan Siap') {
            return {
                icon: 'fa-bag-shopping',
                label: 'Siap Diambil',
                badgeClass: 'bg-blue-50 text-blue-700 border border-blue-200'
            };
        }

        if (normalizedStatus === 'Selesai') {
            return {
                icon: 'fa-check-circle',
                label: 'Selesai',
                badgeClass: 'bg-green-50 text-green-700 border border-green-200'
            };
        }

        if (normalizedStatus === 'Dibatalkan') {
            return {
                icon: 'fa-circle-xmark',
                label: 'Dibatalkan',
                badgeClass: 'bg-red-50 text-red-700 border border-red-200'
            };
        }

        return {
            icon: 'fa-clock',
            label: normalizedStatus || 'Menunggu',
            badgeClass: 'bg-slate-100 text-slate-700 border border-slate-200'
        };
    }

    function getUserPaymentMeta(paymentStatus) {
        if (String(paymentStatus || '').trim() === 'Lunas') {
            return {
                label: 'Sudah Lunas',
                badgeClass: 'bg-green-50 text-green-700 border border-green-200'
            };
        }

        return {
            label: paymentStatus || 'Menunggu Pembayaran',
            badgeClass: 'bg-amber-50 text-amber-700 border border-amber-200'
        };
    }

    function getUserOrderTypeMeta(orderType) {
        const normalizedType = normalizeOrderType(orderType);

        if (normalizedType === 'delivery') {
            return {
                label: 'Delivery',
                icon: 'fa-motorcycle',
                badgeClass: 'bg-sky-50 text-sky-700 border border-sky-200'
            };
        }

        if (normalizedType === 'take-away' || normalizedType === 'takeaway') {
            return {
                label: 'Take Away',
                icon: 'fa-bag-shopping',
                badgeClass: 'bg-orange-50 text-orange-700 border border-orange-200'
            };
        }

        return {
            label: orderType || 'Pesanan',
            icon: 'fa-utensils',
            badgeClass: 'bg-red-50 text-red-700 border border-red-200'
        };
    }

    function renderUserOrdersSummary(orders = []) {
        const summaryContainer = document.getElementById('user-orders-summary');
        if (!summaryContainer) return;

        const totalOrders = orders.length;
        const waitingOrders = orders.filter((order) => {
            return String(order.payment_status || '').trim() !== 'Lunas'
                || String(order.status || '').trim() === 'Menunggu Pembayaran';
        }).length;
        const processingOrders = orders.filter((order) => {
            return ['Diproses', 'Sedang Disiapkan', 'Sedang Diantar', 'Pesanan Siap'].includes(String(order.status || '').trim());
        }).length;
        const completedOrders = orders.filter((order) => String(order.status || '').trim() === 'Selesai').length;

        summaryContainer.innerHTML = `
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                <i class="fas fa-receipt text-[12px]"></i>
                <span class="font-semibold">Total</span>
                <span class="font-bold text-slate-900">${totalOrders}</span>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                <i class="fas fa-hourglass-half text-[12px]"></i>
                <span class="font-semibold">Menunggu</span>
                <span class="font-bold">${waitingOrders}</span>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                <i class="fas fa-fire-burner text-[12px]"></i>
                <span class="font-semibold">Diproses</span>
                <span class="font-bold">${processingOrders}</span>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                <i class="fas fa-check-circle text-[12px]"></i>
                <span class="font-semibold">Selesai</span>
                <span class="font-bold">${completedOrders}</span>
            </div>
        `;
    }

    async function renderUserOrdersTable() {
        const listContainer = document.getElementById('user-orders-list');
        if (!listContainer) return;
        renderUserOrdersSummary([]);
        listContainer.innerHTML = `
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm animate-pulse">
                <div class="h-4 w-28 rounded-full bg-slate-200 mb-3"></div>
                <div class="h-5 w-40 rounded-full bg-slate-200 mb-4"></div>
                <div class="h-4 w-full rounded-full bg-slate-100 mb-2"></div>
                <div class="h-4 w-2/3 rounded-full bg-slate-100 mb-4"></div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <div class="h-8 w-28 rounded-full bg-slate-100"></div>
                    <div class="h-8 w-36 rounded-full bg-slate-100"></div>
                </div>
                <div class="h-px bg-slate-100 mb-3"></div>
                <div class="flex items-center justify-between">
                    <div class="h-4 w-24 rounded-full bg-slate-100"></div>
                    <div class="h-5 w-24 rounded-full bg-slate-200"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm animate-pulse">
                <div class="h-4 w-28 rounded-full bg-slate-200 mb-3"></div>
                <div class="h-5 w-40 rounded-full bg-slate-200 mb-4"></div>
                <div class="h-4 w-full rounded-full bg-slate-100 mb-2"></div>
                <div class="h-4 w-2/3 rounded-full bg-slate-100 mb-4"></div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <div class="h-8 w-28 rounded-full bg-slate-100"></div>
                    <div class="h-8 w-36 rounded-full bg-slate-100"></div>
                </div>
                <div class="h-px bg-slate-100 mb-3"></div>
                <div class="flex items-center justify-between">
                    <div class="h-4 w-24 rounded-full bg-slate-100"></div>
                    <div class="h-5 w-24 rounded-full bg-slate-200"></div>
                </div>
            </div>
        `;

        try {
            const result = await apiRequest('/pesanan/user');
            if (result.success && result.data.length > 0) {
                renderUserOrdersSummary(result.data);
                listContainer.innerHTML = '';
                result.data.forEach(order => {
                    const card = document.createElement('div');
                    card.className = 'rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm transition hover:shadow-md';
                    const orderType = normalizeOrderType(order.jenis);
                    const statusMeta = getUserOrderStatusMeta(order.status);
                    const paymentMeta = getUserPaymentMeta(order.payment_status);
                    const typeMeta = getUserOrderTypeMeta(order.jenis);
                    const paymentMethodLabel = escapeHtml(order.payment_method_label || formatPaymentMethodLabel(order.payment_method));
                    const orderCode = escapeHtml(order.order_code || 'Pesanan Tanpa Kode');
                    const orderDate = escapeHtml(order.date || '-');
                    const orderItems = escapeHtml(order.items || '-');
                    const orderAddress = escapeHtml(order.alamat || '-');
                    const outletLabel = escapeHtml(order.outlet_label || 'Outlet belum dipilih');
                    const totalBelanja = formatRupiah(order.total);

                    card.innerHTML = `
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-slate-500 flex items-center gap-2">
                                        <i class="fas fa-calendar-alt text-[11px]"></i> ${orderDate}
                                    </p>
                                    <h4 class="mt-1 text-lg font-bold text-slate-900">${orderCode}</h4>
                                </div>
                                <div class="flex flex-wrap gap-2 sm:justify-end">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold ${statusMeta.badgeClass}">
                                        <i class="fas ${statusMeta.icon} text-[10px]"></i> ${statusMeta.label}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold ${typeMeta.badgeClass}">
                                        <i class="fas ${typeMeta.icon} text-[10px]"></i> ${escapeHtml(typeMeta.label)}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 mb-1">Menu Dipesan</p>
                                <p class="text-[15px] sm:text-base font-semibold text-slate-800 leading-relaxed">${orderItems}</p>
                            </div>

                            <p class="text-sm text-slate-500 leading-relaxed">
                                <span class="font-medium text-slate-600">Outlet:</span> ${outletLabel}
                            </p>

                            ${orderType === 'delivery' ? `
                                <p class="text-sm text-slate-500 leading-relaxed">
                                    <span class="font-medium text-slate-600">Alamat:</span> ${orderAddress}
                                </p>
                            ` : ''}

                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700">
                                    <i class="fas fa-credit-card text-[11px] text-slate-400"></i> ${paymentMethodLabel}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium ${paymentMeta.badgeClass}">
                                    <i class="fas fa-circle text-[8px]"></i> ${escapeHtml(paymentMeta.label)}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Total Belanja</span>
                                <span class="text-lg sm:text-xl font-bold text-slate-900">${totalBelanja}</span>
                            </div>
                        </div>
                    `;
                    listContainer.appendChild(card);
                });
            } else {
                renderUserOrdersSummary([]);
                listContainer.innerHTML = '<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500 flex flex-col items-center justify-center"><div class="w-14 h-14 rounded-2xl bg-white text-slate-400 flex items-center justify-center mb-4 border border-slate-200"><i class="fas fa-receipt text-xl"></i></div><p class="text-base font-semibold text-slate-700 mb-1">Belum ada pesanan aktif</p><p>Yuk pilih menu favoritmu, nanti riwayat pesanan akan muncul di sini.</p></div>';
            }
        } catch (err) {
            renderUserOrdersSummary([]);
            listContainer.innerHTML = '<div class="rounded-2xl border border-red-100 bg-red-50 p-8 text-center text-sm text-red-600">Gagal memuat pesanan. Coba tutup lalu buka lagi jendela ini.</div>';
        }
    }

    // ====================================================================
    // PENGATURAN LAYOUT (Grid/List)
    // ====================================================================
    // Saat radio button layout diubah → simpan ke localStorage & render ulang
    document.querySelectorAll('input[name="layout_mode"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            localStorage.setItem('menuLayout', e.target.value);
            renderProducts();
        });
    });

    // ====================================================================
    // TAMBAH MENU BARU (Admin Only)
    // ====================================================================

    const fabAddMenu = document.getElementById('btn-add-menu-fab');     // Tombol FAB (+)
    const addMenuModal = document.getElementById('addMenuModal');        // Modal form tambah menu
    const closeAddMenuBtn = document.getElementById('closeAddMenuModal'); // Tombol tutup modal

    // Buka modal tambah menu saat FAB diklik
    if (fabAddMenu) {
        fabAddMenu.addEventListener('click', () => addMenuModal.classList.remove('hidden'));
    }
    // Tutup modal tambah menu
    if (closeAddMenuBtn) {
        closeAddMenuBtn.addEventListener('click', () => addMenuModal.classList.add('hidden'));
    }

    /**
     * Preview gambar saat admin memilih file
     */
    const imgInput = document.getElementById('new_menu_img');
    const imgPreview = document.getElementById('new_menu_img_preview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    imgPreview.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(file);
            } else {
                imgPreview.innerHTML = '<i class="fas fa-image text-2xl text-gray-300"></i>';
            }
        });
    }

    /**
     * Auto-format harga saat mengetik (25000 → 25,000)
     * Hanya izinkan angka, otomatis tambahkan koma pemisah ribuan
     */
    const priceDisplay = document.getElementById('new_menu_price_display');
    const priceHidden = document.getElementById('new_menu_price');
    if (priceDisplay && priceHidden) {
        priceDisplay.addEventListener('input', (e) => {
            // Hapus semua karakter selain angka
            let raw = e.target.value.replace(/[^0-9]/g, '');
            // Simpan nilai angka murni ke hidden input
            priceHidden.value = raw;
            // Format dengan koma pemisah ribuan (25000 → 25,000)
            if (raw) {
                e.target.value = parseInt(raw).toLocaleString('en-US');
            } else {
                e.target.value = '';
            }
        });
    }
    /**
     * Submit form Tambah Menu (dengan upload file gambar):
     * 1. Buat FormData dari form (termasuk file gambar)
     * 2. Kirim ke server via POST '/products' (multipart/form-data)
     * 3. Jika berhasil → tambahkan ke array products & render ulang
     */
    addMenuForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Buat FormData untuk upload file
        const formData = new FormData();
        formData.append('name', document.getElementById('new_menu_name').value);
        formData.append('price', document.getElementById('new_menu_price').value);
        formData.append('description', document.getElementById('new_menu_desc').value);
        formData.append('category', document.getElementById('new_menu_category').value || 'makanan');

        // Tambahkan file gambar jika ada
        const imageFile = document.getElementById('new_menu_img').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            // Ambil CSRF token
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const token = csrfMeta ? csrfMeta.getAttribute('content') : CSRF_TOKEN;

            // Kirim dengan fetch (FormData, tanpa Content-Type header agar browser set boundary)
            const response = await fetch(resolveAppUrl('/products'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Tambahkan produk baru ke array lokal agar langsung terlihat
                products.push({
                    id: result.product.id,
                    name: result.product.name,
                    price: result.product.price,
                    desc: result.product.description,
                    image: result.product.image,
                    badge: result.product.badge,
                    category: result.product.category || 'makanan',
                    reviews: []
                });
                Swal.fire({icon: 'success', title: 'Menu Baru!', text: result.message || 'Wah, menu baru berhasil ditambahkan! Makin mantap nih.', confirmButtonColor: '#D20000'}).then(() => {
                    addMenuModal.classList.add('hidden'); // Tutup modal
                    addMenuForm.reset();                  // Reset form
                    // Reset preview gambar
                    if (imgPreview) imgPreview.innerHTML = '<i class="fas fa-image text-2xl text-gray-300"></i>';
                    renderProducts();                     // Render ulang
                });
            } else {
                Swal.fire({icon: 'error', title: 'Gagal!', text: result.message || 'Menu gagal ditambahkan.', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            Swal.fire({icon: 'error', title: 'Oops!', text: 'Sistem menolak menu barumu. Pastikan isi formnya bener ya bos.', confirmButtonColor: '#D20000'});
        }
    });

    // ====================================================================
    // HAPUS & EDIT PRODUK (Admin Only)
    // ====================================================================

    /**
     * deleteProduct(id) - Hapus produk dari database dan tampilan
     * Mengirim DELETE request ke server, lalu hapus dari array lokal
     */
    window.deleteProduct = async (id) => {
        Swal.fire({
            title: 'Hapus Menu?',
            text: 'Yakin ingin menghapus menu ini secara permanen?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D20000',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await apiRequest(`/products/${id}`, 'DELETE');
                    if (res.success) {
                        products = products.filter(p => p.id !== id);
                        renderProducts();
                        Swal.fire({toast:true, position:'bottom-end', showConfirmButton:false, timer:2000, icon:'success', title:'Menu berhasil dihapus!'});
                    }
                } catch (err) {
                    Swal.fire({icon: 'error', title: 'Waduh..', text: 'Gagal menghapus menu.', confirmButtonColor: '#D20000'});
                }
            }
        });
    };

    /**
     * editProduct(id) - Edit harga produk
     * Menggunakan prompt() untuk input harga baru, lalu kirim PUT request
     */
    window.editProduct = async (id) => {
        const product = products.find(p => p.id === id);
        if (!product) return;

        Swal.fire({
            title: `Update Harga`,
            text: `Masukkan harga baru untuk ${product.name}`,
            input: 'number',
            inputValue: product.price,
            showCancelButton: true,
            confirmButtonColor: '#D20000',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || isNaN(value)) return 'Masukkan harga yang valid ya!';
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const newPrice = result.value;
                try {
                    const res = await apiRequest(`/products/${id}`, 'PUT', { price: parseInt(newPrice) });
                    if (res.success) {
                        product.price = parseInt(newPrice);
                        renderProducts();
                        Swal.fire({icon: 'success', title: 'Mantap!', text: 'Harga menu berhasil diupdate!', confirmButtonColor: '#D20000'});
                    }
                } catch (err) {
                    Swal.fire({icon: 'error', title: 'Waduh..', text: 'Gagal mengubah harga. Coba lagi yuk!', confirmButtonColor: '#D20000'});
                }
            }
        });
    };

    // ====================================================================
    // SISTEM ULASAN (REVIEW)
    // ====================================================================

    const reviewModal = document.getElementById('reviewModal');     // Modal ulasan
    const reviewForm = document.getElementById('reviewForm');       // Form ulasan
    const closeReviewBtn = document.getElementById('closeReviewModal');
    let starRatingValue = 0;   // Nilai rating bintang yang dipilih user

    /**
     * openReviewModal(id) - Buka modal ulasan untuk produk tertentu
     * Menampilkan ulasan yang sudah ada & form untuk tulis ulasan baru
     */
    window.openReviewModal = (id) => {
        // Cek login dulu
        if (!currentUser) {
            Swal.fire({icon: 'info', title: 'Stop!', text: 'Kamu harus login dulu buat ngintip komentar orang-orang atau ngasih rating!', confirmButtonColor: '#D20000', confirmButtonText: 'Oke, Login!'}).then(() => {
                loginModal.classList.remove('hidden');
            });
            return;
        }

        const product = products.find(p => p.id === id);
        if (!product) return;

        // Set ID produk dan nama di modal
        document.getElementById('review_product_id').value = id;
        document.getElementById('review-product-name').innerText = product.name;

        // Render daftar ulasan yang sudah ada
        const reviewsContainer = document.getElementById('existing-reviews');
        reviewsContainer.innerHTML = '';

        if (product.reviews && product.reviews.length > 0) {
            // Tampilkan ulasan terbaru dulu (reverse)
            product.reviews.slice().reverse().forEach(r => {
                const stars = '<i class="fas fa-star text-yellow-500 text-xs"></i>'.repeat(r.rating);
                const reviewItem = document.createElement('div');
                reviewItem.className = "bg-gray-50 p-3 rounded-lg border border-gray-100";
                reviewItem.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-sm text-gray-900">${r.user}</span>
                        <span class="text-xs text-gray-400">${r.date}</span>
                    </div>
                    <div class="flex items-center mb-1">${stars}</div>
                    <p class="text-sm text-gray-600">${r.comment || ''}</p>`;
                reviewsContainer.appendChild(reviewItem);
            });
        } else {
            reviewsContainer.innerHTML = `<p class="text-gray-500 text-sm italic text-center py-4">Belum ada ulasan untuk menu ini.</p>`;
        }

        reviewModal.classList.remove('hidden'); // Buka modal
        resetStarRating();                      // Reset form rating
    };

    // Tutup modal ulasan
    if (closeReviewBtn) {
        closeReviewBtn.addEventListener('click', () => reviewModal.classList.add('hidden'));
    }

    // ====================================================================
    // INPUT RATING BINTANG (Klik bintang 1-5)
    // ====================================================================

    const starInputs = document.querySelectorAll('#star-rating-input i');
    starInputs.forEach(star => {
        star.addEventListener('click', () => {
            starRatingValue = parseInt(star.dataset.value); // Simpan nilai rating
            document.getElementById('review_rating').value = starRatingValue;
            updateStarVisuals(starRatingValue);             // Update tampilan bintang
        });
    });

    /**
     * updateStarVisuals(value) - Update tampilan visual bintang
     * Bintang 1 s/d value: terisi penuh (fas = solid)
     * Bintang di atas value: kosong (far = regular/outline)
     */
    function updateStarVisuals(value) {
        starInputs.forEach(s => {
            const v = parseInt(s.dataset.value);
            if (v <= value) { s.classList.remove('far'); s.classList.add('fas'); }   // Terisi
            else { s.classList.remove('fas'); s.classList.add('far'); }               // Kosong
        });
    }

    /**
     * resetStarRating() - Reset form rating ke keadaan awal (kosong)
     */
    function resetStarRating() {
        starRatingValue = 0;
        document.getElementById('review_rating').value = '';
        document.getElementById('review_comment').value = '';
        updateStarVisuals(0);
    }

    /**
     * Submit form ulasan:
     * 1. Ambil product_id, rating, dan komentar
     * 2. Validasi rating (wajib pilih bintang)
     * 3. Kirim ke server via POST '/reviews'
     * 4. Jika berhasil → tambahkan ulasan ke array lokal & render ulang
     */
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const product_id = parseInt(document.getElementById('review_product_id').value);
        const rating = parseInt(document.getElementById('review_rating').value);
        const comment = document.getElementById('review_comment').value;

        if (!rating) { Swal.fire({icon: 'warning', title: 'Eits..', text: 'Jangan lupa kasih bintangnya dong!', confirmButtonColor: '#f59e0b'}); return; }

        try {
            const result = await apiRequest('/reviews', 'POST', { product_id, rating, comment });
            if (result.success) {
                // Tambahkan ulasan baru ke array lokal
                const product = products.find(p => p.id === product_id);
                if (product) {
                    if (!product.reviews) product.reviews = [];
                    product.reviews.push(result.review);
                }
                Swal.fire({icon: 'success', title: 'Terima Kasih!', text: result.message || 'Ulasanmu sangat berarti buat kemajuan kami!', confirmButtonColor: '#f59e0b'}).then(() => {
                    reviewModal.classList.add('hidden'); // Tutup modal
                    renderProducts();                    // Render ulang (rating terupdate)
                });
            }
        } catch (err) {
            Swal.fire({icon: 'error', title: 'Gagal Ngirim', text: 'Hayo.. ulasan kamu nyangkut. Cek koneksi ya!', confirmButtonColor: '#f59e0b'});
        }
    });

    // ====================================================================
    // HANDLER KLIK TOMBOL KERANJANG DI CARD PRODUK
    // ====================================================================

    /**
     * handleCartClick(e) - Menangani klik pada tombol "Tambah ke Keranjang"
     *
     * 1. Cari ID produk dari card parent-nya
     * 2. Cek apakah user sudah login
     * 3. Jika ya → tambahkan ke cart
     * 4. Jika tidak → minta login dulu
     */
    function handleCartClick(e) {
        e.preventDefault();
        const btn = e.target.closest('.btn-cart');       // Cari tombol cart terdekat
        const card = btn.closest('.product-card');       // Cari card produk parent
        const id = parseInt(card.dataset.id);            // Ambil ID produk

        if (!currentUser) {
            Swal.fire({icon: 'info', title: 'Lapar Ya?', text: 'Login dulu yuk sebelum masukin makanan enak ke keranjang!', confirmButtonColor: '#D20000', confirmButtonText: 'Meluncur!'}).then(() => {
                loginModal.classList.remove('hidden');
            });
            return;
        }

        addToCart(id); // Tambahkan ke keranjang
    }

    // ====================================================================
    // TUTUP SEMUA MODAL (Tombol × / Close)
    // ====================================================================

    // Tutup modal login/signup/admin saat tombol close diklik
    document.getElementById('closeLoginModal').addEventListener('click', () => loginModal.classList.add('hidden'));
    document.getElementById('closeSignupModal').addEventListener('click', () => signupModal.classList.add('hidden'));
    document.getElementById('closeAdminModal').addEventListener('click', () => adminModal.classList.add('hidden'));

    // Link "Daftar disini" di modal login → buka modal signup
    const signUpLink = document.getElementById('link-signup');
    if (signUpLink) {
        signUpLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.classList.add('hidden');        // Tutup modal login
            signupModal.classList.remove('hidden');    // Buka modal signup
        });
    }

    // ====================================================================
    // PENGATURAN USER (Settings Modal)
    // ====================================================================

    const settingsModal = document.getElementById('settingsModal');
    const settingsForm = document.getElementById('settingsForm');
    const settingsNotLoggedIn = document.getElementById('settings-not-logged-in');

    /**
     * openSettingsModal() - Buka modal settings dan isi data user
     * Jika belum login → tampilkan pesan "silakan login"
     * Jika sudah login → isi form dengan data user saat ini
     */
    function openSettingsModal() {
        if (!settingsModal) return;

        if (currentUser) {
            // User sudah login: tampilkan form, isi dengan data
            settingsNotLoggedIn.classList.add('hidden');
            settingsForm.classList.remove('hidden');
            document.getElementById('settings_name').value = currentUser.name || '';
            document.getElementById('settings_username').value = currentUser.username || '';
            document.getElementById('settings_no_hp').value = currentUser.no_hp || '';
            document.getElementById('settings_alamat').value = currentUser.alamat || '';
            document.getElementById('settings_password').value = '';
        } else {
            // User belum login: tampilkan pesan
            settingsForm.classList.add('hidden');
            settingsNotLoggedIn.classList.remove('hidden');
        }

        settingsModal.classList.remove('hidden');
    }

    // Tombol settings di header (desktop)
    document.getElementById('btn-settings').addEventListener('click', () => openSettingsModal());

    // Tutup modal settings
    document.getElementById('closeSettingsModal').addEventListener('click', () => {
        settingsModal.classList.add('hidden');
    });

    // Tombol "Login Sekarang" di settings modal (saat belum login)
    const settingsGoLogin = document.getElementById('settings-go-login');
    if (settingsGoLogin) {
        settingsGoLogin.addEventListener('click', () => {
            settingsModal.classList.add('hidden');
            loginModal.classList.remove('hidden');
        });
    }

    // ====================================================================
    // TAB SLIDER PENGATURAN (PROFIL VS BANNER) - ADMIN ONLY
    // ====================================================================
    const btnTabProfile = document.getElementById('btn-tab-profile');
    const btnTabBanner = document.getElementById('btn-tab-banner');
    const btnTabOutlet = document.getElementById('btn-tab-outlet');
    const containerProfile = document.getElementById('container-profile');
    const containerBanner = document.getElementById('container-banner');
    const containerOutlet = document.getElementById('container-outlet');

    if (btnTabProfile && btnTabBanner && btnTabOutlet) {
        const resetTabs = () => {
            [btnTabProfile, btnTabBanner, btnTabOutlet].forEach(btn => btn.className = 'flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-gray-700 transition-all');
            [containerProfile, containerBanner, containerOutlet].forEach(c => c.classList.add('hidden'));
        };

        btnTabProfile.addEventListener('click', () => {
            resetTabs();
            btnTabProfile.className = 'flex-1 py-2 rounded-lg bg-white shadow-sm text-sm font-bold text-red-600 transition-all';
            containerProfile.classList.remove('hidden');
        });

        btnTabBanner.addEventListener('click', () => {
            resetTabs();
            btnTabBanner.className = 'flex-1 py-2 rounded-lg bg-white shadow-sm text-sm font-bold text-red-600 transition-all';
            containerBanner.classList.remove('hidden');
            if (typeof renderAdminBanners === 'function') renderAdminBanners();
        });

        btnTabOutlet.addEventListener('click', () => {
            resetTabs();
            btnTabOutlet.className = 'flex-1 py-2 rounded-lg bg-white shadow-sm text-sm font-bold text-red-600 transition-all';
            containerOutlet.classList.remove('hidden');
        });
    }

    // Submit form settings (update profil)
    settingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            name: document.getElementById('settings_name').value,
            username: document.getElementById('settings_username').value,
            no_hp: document.getElementById('settings_no_hp').value,
            alamat: document.getElementById('settings_alamat').value,
        };

        // Tambahkan password hanya jika diisi
        const newPassword = document.getElementById('settings_password').value;
        if (newPassword) {
            data.password = newPassword;
        }

        try {
            const result = await apiRequest('/api/user/update', 'PUT', data);
            if (result.success) {
                // Update data user lokal
                currentUser.name = data.name;
                currentUser.username = data.username;
                currentUser.no_hp = data.no_hp;
                currentUser.alamat = data.alamat;
                Swal.fire({icon: 'success', title: 'Mantap!', text: result.message || 'Profil berhasil di-makeover! Keren kan.', confirmButtonColor: '#D20000'}).then(() => {
                    settingsModal.classList.add('hidden');
                    updateLoginUI();
                });
            } else {
                Swal.fire({icon: 'error', title: 'Gagal Update', text: result.message || 'Waduh, data profilnya nolak buat diganti nih.', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            Swal.fire({icon: 'error', title: 'Oops!', text: 'Terjadi kesalahan sistem saat ngerombak profilmu!', confirmButtonColor: '#D20000'});
        }
    });

    const formEditOutlet = document.getElementById('form-edit-outlet');
    if (formEditOutlet) {
        formEditOutlet.addEventListener('submit', async (e) => {
            e.preventDefault();
            const outlet_address = document.getElementById('input_outlet_address').value;
            const admin_whatsapp_number = document.getElementById('input_admin_whatsapp').value;
            const payment_qris_label = document.getElementById('input_payment_qris_label').value;
            const payment_qris_image_url = document.getElementById('input_payment_qris_image_url').value;
            const payment_qris_note = document.getElementById('input_payment_qris_note').value;
            const payment_bank_name = document.getElementById('input_payment_bank_name').value;
            const payment_bank_account_number = document.getElementById('input_payment_bank_account_number').value;
            const payment_bank_account_name = document.getElementById('input_payment_bank_account_name').value;
            const payment_bank_note = document.getElementById('input_payment_bank_note').value;

            try {
                const result = await apiRequest('/admin/settings', 'POST', {
                    outlet_address,
                    admin_whatsapp_number,
                    payment_qris_label,
                    payment_qris_image_url,
                    payment_qris_note,
                    payment_bank_name,
                    payment_bank_account_number,
                    payment_bank_account_name,
                    payment_bank_note
                });
                if (result.success) {
                    Swal.fire({icon: 'success', title: 'Berhasil!', text: result.message, confirmButtonColor: '#D20000'});
                    // Update tampilan di kontak
                    document.getElementById('display-outlet-address').innerHTML = `<i class="fas fa-map-marker-alt mr-2"></i> ${outlet_address}`;
                } else {
                    Swal.fire({icon: 'error', title: 'Gagal', text: result.message || 'Gagal mengubah alamat outlet.', confirmButtonColor: '#D20000'});
                }
            } catch (err) {
                Swal.fire({icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#D20000'});
            }
        });
    }

    // Offset header untuk smooth scroll (tinggi header fixed dalam px)
    const HEADER_OFFSET = 100;

    // ====================================================================
    // MOBILE DRAWER (Menu Navigasi untuk Handphone)
    // ====================================================================
    // Drawer adalah panel geser dari kanan yang muncul di layar kecil (mobile)
    // Menggantikan navbar horizontal yang terlalu sempit di layar kecil

    const hamburgerBtn = document.querySelector('.hamburger-btn');   // Tombol hamburger (☰)
    const mobileDrawer = document.querySelector('.mobile-drawer');   // Panel drawer
    const drawerOverlay = document.querySelector('.drawer-overlay'); // Overlay gelap di belakang drawer
    const drawerClose = document.querySelector('.drawer-close');     // Tombol × di drawer

    /**
     * openDrawer() - Buka drawer mobile
     * Menambahkan class 'open' yang memicu animasi CSS translateX(0)
     * Juga mengunci scroll halaman agar tidak bisa di-scroll saat drawer terbuka
     */
    function openDrawer() {
        mobileDrawer.classList.add('open');        // Animasi geser masuk
        drawerOverlay.classList.add('open');       // Tampilkan overlay gelap
        hamburgerBtn.classList.add('active');      // Animasi hamburger → X
        document.body.style.overflow = 'hidden';  // Kunci scroll halaman
    }

    /**
     * closeDrawer() - Tutup drawer mobile
     */
    function closeDrawer() {
        mobileDrawer.classList.remove('open');     // Animasi geser keluar
        drawerOverlay.classList.remove('open');    // Sembunyikan overlay
        hamburgerBtn.classList.remove('active');   // Animasi X → hamburger
        document.body.style.overflow = '';         // Buka kunci scroll
    }

    // Toggle drawer saat hamburger diklik (buka jika tutup, tutup jika buka)
    if (hamburgerBtn) hamburgerBtn.addEventListener('click', () => {
        mobileDrawer.classList.contains('open') ? closeDrawer() : openDrawer();
    });
    // Tutup drawer saat overlay diklik
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);
    // Tutup drawer saat tombol × diklik
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);

    // ====================================================================
    // TOMBOL-TOMBOL DI DRAWER MOBILE
    // ====================================================================
    // Tombol di drawer adalah "proxy" yang memanggil tombol asli di header

    const btnLoginMobile = document.getElementById('btn-login-mobile');
    const btnSettingsMobile = document.getElementById('btn-settings-mobile');
    const btnAdminPanelMobile = document.getElementById('btn-admin-panel-mobile');

    // Klik login di drawer → tutup drawer → klik tombol login asli
    if (btnLoginMobile) btnLoginMobile.addEventListener('click', () => {
        closeDrawer();
        btnLoginHeader.click();
    });
    // Klik settings di drawer → tutup drawer → klik tombol settings asli
    if (btnSettingsMobile) btnSettingsMobile.addEventListener('click', () => {
        closeDrawer();
        document.getElementById('btn-settings').click();
    });
    // Klik admin di drawer → tutup drawer → klik tombol admin asli
    if (btnAdminPanelMobile) btnAdminPanelMobile.addEventListener('click', (e) => {
        e.preventDefault();
        closeDrawer();
        btnAdminPanel.click();
    });

    // ====================================================================
    // UPDATE TAMPILAN DRAWER MOBILE (Sesuai Status Login)
    // ====================================================================

    /**
     * updateMobileDrawerUI() - Update drawer sesuai status login
     *
     * Jika login: tampilkan nama user & menu admin (jika admin)
     * Jika logout: tampilkan tombol "Login" & sembunyikan menu admin
     */
    function updateMobileDrawerUI() {
        const navAdminMobile = document.getElementById('nav-admin-mobile');
        if (currentUser) {
            // User sudah login
            if (btnLoginMobile) {
                btnLoginMobile.querySelector('i').classList.remove('fa-sign-in-alt');
                btnLoginMobile.querySelector('i').classList.add('fa-user');
                btnLoginMobile.querySelector('span').textContent = currentUser.name; // Tampilkan nama
            }
            // Tampilkan menu admin jika role admin
            if (currentUser.role === 'admin' && navAdminMobile) {
                navAdminMobile.classList.remove('hidden');
            }
        } else {
            // User belum login
            if (btnLoginMobile) {
                btnLoginMobile.querySelector('i').classList.remove('fa-user');
                btnLoginMobile.querySelector('i').classList.add('fa-sign-in-alt');
                btnLoginMobile.querySelector('span').textContent = 'Login';
            }
            if (navAdminMobile) navAdminMobile.classList.add('hidden');
        }
    }

    // ====================================================================
    // SMOOTH SCROLL - Link di Drawer Mobile
    // ====================================================================
    // Saat link navigasi di drawer diklik, scroll halaman ke section tujuan
    // dengan animasi smooth (halus), bukan langsung loncat

    document.querySelectorAll('.nav-links-mobile a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            closeDrawer(); // Tutup drawer dulu
            const targetId = link.getAttribute('href');       // Ambil ID target (misal: #menu)
            const targetSection = document.querySelector(targetId); // Cari section tujuan
            if (targetSection) {
                // Hitung posisi scroll (dikurangi tinggi header agar tidak tertutup)
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });

    // ====================================================================
    // SMOOTH SCROLL - Link Navbar Desktop
    // ====================================================================
    // Sama seperti drawer, tapi untuk link di navbar desktop

    document.querySelectorAll('.nav-links a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'   // Animasi scroll halus
                });
            }
        });
    });

    // ====================================================================
    // SMOOTH SCROLL - Link Lain (selain navbar)
    // ====================================================================
    // Untuk link-link lain yang mengarah ke section (misal tombol CTA)

    document.querySelectorAll('a[href^="#"]').forEach(link => {
        // Skip link yang sudah di-handle di atas atau link tanpa target
        if (link.closest('.nav-links') || link.getAttribute('href') === '#') return;

        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                e.preventDefault();
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ====================================================================
    // HIGHLIGHT LINK NAVBAR AKTIF SAAT SCROLL
    // ====================================================================
    // Saat user scroll, link navbar yang sesuai dengan section yang terlihat
    // akan diberi warna merah (highlight)

    const sections = document.querySelectorAll('section[id]');              // Semua section dengan ID
    const navLinksAll = document.querySelectorAll('.nav-links a[href^="#"]'); // Semua link navbar

    /**
     * updateActiveNavLink() - Cek section mana yang sedang terlihat
     * dan highlight link navbar yang sesuai
     */
    function updateActiveNavLink() {
        const scrollPos = window.scrollY + HEADER_OFFSET + 50; // Posisi scroll + offset

        sections.forEach(section => {
            const sectionTop = section.offsetTop;          // Posisi atas section
            const sectionHeight = section.offsetHeight;    // Tinggi section
            const sectionId = section.getAttribute('id');  // ID section

            // Jika posisi scroll ada di dalam range section ini
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                // Hapus highlight dari semua link
                navLinksAll.forEach(link => {
                    link.classList.remove('text-primary-red', 'font-extrabold');
                    link.style.transform = '';
                    link.style.textShadow = '';
                });

                // Berikan highlight ke link yang sesuai
                const activeLink = document.querySelector(`.nav-links a[href="#${sectionId}"]`);
                if (activeLink) {
                    activeLink.classList.add('text-primary-red');
                    activeLink.style.textShadow = '0 0 8px rgba(210, 0, 0, 0.3)'; // Efek glow merah
                }
            }
        });
    }

    // ====================================================================
    // THROTTLE SCROLL EVENT (Optimasi Performa)
    // ====================================================================
    // Tanpa throttle, fungsi akan dipanggil ratusan kali per detik saat scroll.
    // Dengan throttle (50ms), fungsi hanya dipanggil maksimal 20 kali per detik.

    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) return;  // Jika masih menunggu, abaikan
        scrollTimeout = setTimeout(() => {
            updateActiveNavLink();   // Jalankan fungsi
            scrollTimeout = null;    // Reset timeout
        }, 50);                      // Delay 50ms
    });

    // Set link aktif saat halaman pertama kali dimuat
    updateActiveNavLink();

    // ====================================================================
    // INISIALISASI AWAL - Render Produk & Cek Login
    // ====================================================================
    // Urutan penting:
    // 1. Render produk LANGSUNG (tanpa tunggu cek auth) agar menu cepat muncul
    // 2. Cek auth di BACKGROUND (akan re-render jika ternyata ada session login)

    renderProducts();  // Tampilkan menu langsung tanpa tunggu auth
    checkAuth();       // Cek login di background (akan re-render jika ada session)
});
