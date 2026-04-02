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
async function apiRequest(url, method = 'GET', data = null) {
    // Ambil CSRF token terbaru dari meta tag (untuk menghindari token expired)
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute('content') : CSRF_TOKEN;

    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',    // Beritahu server bahwa data berformat JSON
            'X-CSRF-TOKEN': token,                  // Token keamanan Laravel (dari meta tag)
            'Accept': 'application/json',           // Minta response dalam format JSON
        },
        credentials: 'same-origin',                // Kirim cookie session agar auth bekerja
    };

    // Jika ada data, ubah menjadi string JSON dan masukkan ke body request
    if (data) options.body = JSON.stringify(data);

    // Kirim request dan tunggu response
    const response = await fetch(url, options);

    // Jika CSRF token expired (419), reload halaman untuk mendapatkan token baru
    if (response.status === 419) {
        Swal.fire({
            icon: 'warning',
            title: 'Sesi Kedaluwarsa',
            text: 'Halaman akan dimuat ulang untuk memperbarui sesi.',
            confirmButtonColor: '#D20000',
            confirmButtonText: 'Oke'
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
        throw new Error('Server mengembalikan response yang tidak valid.');
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

    // Elemen-elemen carousel (slider produk)
    const carouselPrev = document.getElementById('carousel-prev');    // Tombol prev carousel
    const carouselNext = document.getElementById('carousel-next');    // Tombol next carousel
    const carouselDots = document.getElementById('carousel-dots');    // Dots navigasi carousel

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
            const imgSrc = item.image.startsWith('http') ? item.image : '/' + item.image;
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

        // Opsi alamat tersimpan
        const addressOptions = document.getElementById('address-options');       // Container opsi alamat
        const savedAddressPreview = document.getElementById('saved-address-preview'); // Preview teks alamat
        const useAddressCheckbox = document.getElementById('use_saved_address');  // Checkbox "gunakan alamat tersimpan"
        const alamatField = document.getElementById('checkout_alamat');           // Text area alamat

        // Jika user memiliki alamat tersimpan di akun
        if (currentUser.alamat) {
            addressOptions.classList.remove('hidden'); // Tampilkan opsi alamat

            // Tampilkan preview alamat (potong jika terlalu panjang)
            savedAddressPreview.textContent = currentUser.alamat.length > 50
                ? currentUser.alamat.substring(0, 50) + '...'
                : currentUser.alamat;

            // Otomatis centang dan isi alamat tersimpan
            useAddressCheckbox.checked = true;
            alamatField.value = currentUser.alamat;

            // Event: saat checkbox diubah, toggle isi alamat
            useAddressCheckbox.onchange = () => {
                if (useAddressCheckbox.checked) {
                    alamatField.value = currentUser.alamat; // Isi alamat dari akun
                } else {
                    alamatField.value = '';                  // Kosongkan untuk input manual
                }
            };
        } else {
            // User tidak punya alamat tersimpan
            addressOptions.classList.add('hidden');
            alamatField.value = '';
        }
    });

    // Tombol "Kembali" dari checkout → tampilkan cart lagi
    btnBackToCart.addEventListener('click', () => {
        cartCheckoutSection.classList.add('hidden');      // Sembunyikan form checkout
        cartActionButtons.classList.remove('hidden');     // Tampilkan tombol cart
    });

    // ====================================================================
    // KIRIM PESANAN (Submit Form Checkout)
    // ====================================================================
    // async karena menggunakan fetch API (AJAX request ke server)
    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Cegah form submit biasa (reload halaman)

        // Kumpulkan data dari form checkout
        const data = {
            nama: document.getElementById('checkout_nama').value,
            no_hp: document.getElementById('checkout_no_hp').value,
            alamat: document.getElementById('checkout_alamat').value,
            jenis_belanja: document.getElementById('checkout_jenis').value,
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
                Swal.fire({icon: 'success', title: 'Pesanan Berhasil!', text: result.message || 'Hore! Makanan enakmu segera disiapkan!', confirmButtonColor: '#D20000'}).then(() => {
                    clearCart();                                      // Kosongkan keranjang
                    cartModal.classList.add('hidden');               // Tutup modal cart
                    checkoutForm.reset();                            // Reset form
                    cartCheckoutSection.classList.add('hidden');     // Sembunyikan form checkout
                    cartActionButtons.classList.remove('hidden');    // Tampilkan tombol cart
                });
            } else {
                Swal.fire({icon: 'error', title: 'Gagal Pesan', text: result.message || 'Waduh, pesananmu gagal gaes. Coba lagi yuk!', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            Swal.fire({icon: 'error', title: 'Periksa Datanya!', text: 'Wah, sepertinya ada data yang belum lengkap nih. Yuk cek lagi!', confirmButtonColor: '#D20000'});
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
            const imgSrc = product.image.startsWith('http') ? product.image : '/' + product.image;

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
            stopOrderPolling();
        }

        updateMobileDrawerUI();
        renderProducts();

        // Mulai/hentikan polling notifikasi pesanan
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
            const result = await apiRequest('/login', 'POST', { username, password });
            if (result.success) {
                currentUser = result.user;        // Simpan data user
                Swal.fire({icon: 'success', title: 'Yeay Berhasil!', text: result.message || 'Selamat datang di dunia penuh kelezatan, Chi-Pok!', confirmButtonColor: '#D20000'}).then(() => {
                    updateLoginUI();                   // Update tampilan
                    loginModal.classList.add('hidden'); // Tutup modal login
                    loginForm.reset();                 // Kosongkan form
                });
            } else {
                Swal.fire({icon: 'error', title: 'Oops!', text: result.message || 'Username atau password salah!', confirmButtonColor: '#D20000'});
            }
        } catch (err) {
            // Jangan tampilkan error jika CSRF expired (sudah di-handle di apiRequest)
            if (err.message === 'CSRF token expired') return;
            Swal.fire({icon: 'error', title: 'Koneksi Bermasalah!', text: 'Gagal terhubung ke server. Periksa koneksi internetmu ya!', confirmButtonColor: '#D20000'});
        }
    });

    // ====================================================================
    // PROSES REGISTRASI (DAFTAR AKUN BARU)
    // ====================================================================

    /**
     * Saat form signup di-submit:
     * 1. Ambil semua data dari input (nama, username, password, alamat, no HP)
     * 2. Kirim ke server via AJAX POST ke '/register'
     * 3. Jika berhasil → tutup modal signup, buka modal login
     */
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('signup_name').value;
        const username = document.getElementById('signup_username').value;
        const password = document.getElementById('signup_password').value;
        const alamat = document.getElementById('signup_alamat').value;
        const no_hp = document.getElementById('signup_no_hp').value;

        try {
            const result = await apiRequest('/register', 'POST', { name, username, password, alamat, no_hp });
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
            if (err.message === 'CSRF token expired') return;
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
                    await apiRequest('/logout', 'POST');
                    currentUser = null;
                    updateLoginUI();
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        icon: 'info',
                        title: 'Sampai jumpa lagi!'
                    });
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

    /**
     * renderOrdersTable() - Muat dan tampilkan data pesanan di tabel admin
     *
     * Mengambil data pesanan dari server ('/admin/pesanan')
     * dan menampilkannya dalam bentuk tabel HTML
     */
    async function renderOrdersTable() {
        const tbody = document.getElementById('orders-table-body');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500">Memuat...</td></tr>';

        try {
            const result = await apiRequest('/admin/pesanan');
            if (result.success && result.data.length > 0) {
                tbody.innerHTML = '';
                result.data.forEach(order => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${order.date}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${order.customerName}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${order.no_hp || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-[150px] truncate" title="${order.alamat || '-'}">${order.alamat || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px]">${order.items}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${order.jenis === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'}">${order.jenis || '-'}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900">${formatRupiah(order.total)}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 status-cell">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">${order.status}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            ${getActionHtml(order.group_id, order.jenis, order.status)}
                        </td>`;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-4 text-center text-sm text-gray-500">Belum ada pesanan masuk.</td></tr>';
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-4 text-center text-sm text-red-500">Gagal memuat data pesanan.</td></tr>';
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
            const result = await apiRequest('/admin/pesanan/status', 'PUT', {
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
            const result = await apiRequest('/admin/pesanan');
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

    async function renderUserOrdersTable() {
        const listContainer = document.getElementById('user-orders-list');
        if (!listContainer) return;
        listContainer.innerHTML = '<div class="p-6 text-center text-sm text-gray-500">Memuat pesanan...</div>';

        try {
            const result = await apiRequest('/pesanan/user');
            if (result.success && result.data.length > 0) {
                listContainer.innerHTML = '';
                result.data.forEach(order => {
                    const card = document.createElement('div');
                    card.className = 'bg-white border rounded-xl p-4 shadow-sm relative overflow-hidden transition hover:shadow-md';

                    // Beri warna status menarik
                    let statusColor = 'bg-gray-100 text-gray-800 border-gray-200';
                    let statusIcon = 'fa-clock';
                    if (order.status === 'Sedang Disiapkan') {
                        statusColor = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                        statusIcon = 'fa-fire-burner';
                    }
                    if (order.status === 'Sedang Diantar') {
                        statusColor = 'bg-blue-50 text-blue-700 border border-blue-200';
                        statusIcon = 'fa-motorcycle';
                    }
                    if (order.status === 'Pesanan Siap') {
                        statusColor = 'bg-blue-50 text-blue-700 border border-blue-200';
                        statusIcon = 'fa-shopping-bag';
                    }
                    if (order.status === 'Selesai') {
                        statusColor = 'bg-green-50 text-green-700 border border-green-200';
                        statusIcon = 'fa-check-circle';
                    }

                    card.innerHTML = `
                        <div class="flex justify-between items-start mb-3 border-b pb-3">
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1"><i class="fas fa-calendar-alt opacity-70"></i> ${order.date}</p>
                                <span class="px-2 py-1 inline-flex text-[10px] font-bold rounded-md ${order.jenis === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'} uppercase">
                                    ${order.jenis || '-'}
                                </span>
                            </div>
                            <span class="px-3 py-1.5 inline-flex items-center gap-1.5 text-xs font-bold rounded-full ${statusColor}">
                                <i class="fas ${statusIcon}"></i> ${order.status}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-utensils text-xl"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug">${order.items}</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg mt-2">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Belanja</span>
                            <span class="text-base font-black text-gray-900">${formatRupiah(order.total)}</span>
                        </div>
                    `;
                    listContainer.appendChild(card);
                });
            } else {
                listContainer.innerHTML = '<div class="p-8 text-center text-sm text-gray-500 flex flex-col items-center justify-center"><i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>Belum ada riwayat pesanan aktif. Yuk pesan sekarang!</div>';
            }
        } catch (err) {
            listContainer.innerHTML = '<div class="p-6 text-center text-sm text-red-500">Gagal memuat pesanan.</div>';
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
            const response = await fetch('/products', {
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
    // HEADER MENGECIL SAAT SCROLL
    // ====================================================================
    // Saat user scroll ke bawah > 50px, header mendapat class 'scrolled'
    // yang membuat logo lebih kecil (didefinisikan di CSS)

    const mainHeader = document.getElementById('main-header');
    window.addEventListener('scroll', () => {
        if (mainHeader) {
            if (window.scrollY > 50) {
                mainHeader.classList.add('scrolled');    // Header mengecil
            } else {
                mainHeader.classList.remove('scrolled'); // Header ukuran normal
            }
        }
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
