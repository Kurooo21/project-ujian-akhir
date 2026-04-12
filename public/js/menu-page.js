// ========================================================================
// Chi-Pok - Script untuk Halaman Menu Lengkap (/menu)
// ========================================================================
//
// File ini mengatur tampilan SEMUA produk di halaman /menu yang terpisah.
// Berbeda dengan app.js yang punya carousel, di sini semua produk
// ditampilkan dalam grid penuh tanpa pagination.
//
// FITUR UTAMA:
// - Menampilkan semua produk dalam grid responsif
// - Filter berdasarkan kategori (Semua / Makanan / Minuman)
// - Rating bintang dari ulasan pelanggan
// - Animasi card saat muncul
//
// DATA: Produk diambil dari variabel PRODUCTS_DATA yang di-inject
//        oleh Laravel Blade menggunakan @json($productsData)
// ========================================================================

// Variabel global untuk menyimpan data produk (dari server via Blade)
let products = PRODUCTS_DATA;

// Kategori yang sedang dipilih ('semua' = tampilkan semua produk)
let currentCategory = 'semua';

/**
 * formatRupiah(number) - Mengubah angka menjadi format mata uang Rupiah
 * @param {number} number - Angka yang ingin diformat
 * @returns {string} - String dalam format "Rp 25.000" dll
 *
 * Contoh: formatRupiah(25000) → "Rp 25.000,00"
 *
 * Menggunakan Intl.NumberFormat bawaan JavaScript untuk format
 * angka sesuai standar Indonesia (id-ID)
 */
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
}

/**
 * resolveAppUrl(path) - Normalisasi URL agar tetap benar meski aplikasi dijalankan di subfolder.
 * (APP_BASE_URL diinjek oleh Blade di `resources/views/menu.blade.php`)
 */
function resolveAppUrl(path) {
    if (!path) return path;
    if (/^https?:\/\//i.test(path)) return path;

    const base = (typeof APP_BASE_URL !== 'undefined' && APP_BASE_URL)
        ? String(APP_BASE_URL).replace(/\/+$/, '')
        : '';

    if (!base) return path;

    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    return base + normalizedPath;
}

function resolveAssetUrl(path) {
    if (!path) return path;
    if (/^https?:\/\//i.test(path)) return path;
    const normalized = String(path).startsWith('/') ? String(path) : `/${path}`;
    return resolveAppUrl(normalized);
}

// ========================================================================
// MULAI SAAT HALAMAN SIAP (DOM Content Loaded)
// ========================================================================
document.addEventListener("DOMContentLoaded", () => {

    // Ambil elemen grid menu (tempat card produk akan ditampilkan)
    const menuGrid = document.getElementById('full-menu-grid');

    // Ambil elemen penghitung produk (menampilkan "Menampilkan X menu")
    const productCount = document.getElementById('product-count');

    // ====================================================================
    // FUNGSI FILTER PRODUK BERDASARKAN KATEGORI
    // ====================================================================

    /**
     * getFilteredProducts() - Mendapatkan produk yang sudah difilter
     * @returns {Array} - Array produk yang sesuai kategori terpilih
     *
     * Jika kategori 'semua', kembalikan semua produk.
     * Jika tidak, filter berdasarkan property 'category' produk.
     * Default kategori produk adalah 'makanan' jika tidak di-set.
     */
    function getFilteredProducts() {
        if (currentCategory === 'semua') return products;
        return products.filter(p => (p.category || 'makanan') === currentCategory);
    }

    // ====================================================================
    // FUNGSI RENDER (MENAMPILKAN) PRODUK KE HALAMAN
    // ====================================================================

    /**
     * renderMenuProducts() - Render semua produk ke dalam grid
     *
     * Cara kerja:
     * 1. Kosongkan grid terlebih dahulu
     * 2. Ambil produk yang sudah difilter
     * 3. Update teks jumlah produk
     * 4. Jika tidak ada produk, tampilkan pesan kosong
     * 5. Jika ada, buat card HTML untuk setiap produk
     * 6. Tambahkan animasi masuk (fade-in + slide-up)
     */
    function renderMenuProducts() {
        // Kosongkan isi grid sebelum mengisi ulang
        menuGrid.innerHTML = '';

        // Ambil produk sesuai filter kategori yang aktif
        const filtered = getFilteredProducts();

        // Tampilkan jumlah produk yang ditemukan
        productCount.textContent = `Menampilkan ${filtered.length} menu`;

        // Jika tidak ada produk di kategori ini, tampilkan pesan kosong
        if (filtered.length === 0) {
            menuGrid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <i class="fas fa-utensils text-6xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-400 text-lg">Belum ada menu di kategori ini.</p>
                </div>`;
            return; // Hentikan fungsi di sini (tidak perlu lanjut)
        }

        // Loop setiap produk dan buat card HTML-nya
        filtered.forEach((product, index) => {
            // Buat elemen div untuk card produk
            const card = document.createElement('div');

            // Set class CSS untuk tampilan card (rounded, shadow, hover effect, dll)
            card.className = "bg-white rounded-2xl p-4 text-center shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl relative group flex flex-col justify-between overflow-hidden border border-gray-100";

            // ============================================================
            // RATING HTML - Menampilkan bintang rating dari ulasan
            // ============================================================
            let ratingHtml = '';

            if (product.reviews && product.reviews.length > 0) {
                // Hitung rata-rata rating dari semua ulasan
                const totalReviews = product.reviews.length;
                const sum = product.reviews.reduce((acc, curr) => acc + parseInt(curr.rating), 0);
                const avgRating = (sum / totalReviews).toFixed(1); // Bulatkan 1 desimal

                // Buat HTML bintang (★) sesuai rata-rata rating
                let stars = '';
                const fullStars = Math.floor(avgRating);         // Bintang penuh
                const halfStar = avgRating % 1 >= 0.5 ? 1 : 0;  // Setengah bintang
                for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star text-yellow-500"></i>';
                if (halfStar) stars += '<i class="fas fa-star-half-alt text-yellow-500"></i>';

                // HTML untuk menampilkan rating (bintang + angka + jumlah ulasan)
                ratingHtml = `
                    <div class="flex justify-center items-center gap-1 text-sm mt-2">
                        <div class="flex text-yellow-500">${stars}</div>
                        <span class="font-bold text-text-dark ml-1">${avgRating}</span>
                        <span class="text-gray-400 text-xs">(${totalReviews})</span>
                    </div>`;
            } else {
                // Jika belum ada ulasan, tampilkan teks "Belum ada ulasan"
                ratingHtml = `<div class="flex justify-center items-center gap-1 text-sm mt-2 text-gray-400"><i class="far fa-star"></i> <span class="text-xs">Belum ada ulasan</span></div>`;
            }

            // ============================================================
            // BADGE - Label khusus (misal: "BEST SELLER", "BARU")
            // ============================================================
            let badgeHtml = '';
            if (product.badge) {
                // Badge ditampilkan di pojok kiri atas card
                badgeHtml = `<div class="absolute top-2 left-2 bg-red-100 text-red-600 border border-red-200 py-0.5 px-3 text-[10px] font-bold rounded-full z-10 tracking-wide shadow-sm">${product.badge}</div>`;
            }

            // ============================================================
            // KATEGORI - Icon dan label untuk makanan/minuman
            // ============================================================
            // Tentukan icon dan label berdasarkan kategori produk
            const categoryIcon = (product.category || 'makanan') === 'minuman' ? 'fa-glass-water' : 'fa-drumstick-bite';
            const categoryLabel = (product.category || 'makanan') === 'minuman' ? 'Minuman' : 'Makanan';
            const categoryColor = (product.category || 'makanan') === 'minuman' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600';

            // ============================================================
            // PATH GAMBAR - Tentukan sumber gambar produk
            // ============================================================
            // Jika gambar mulai dengan 'http' = URL eksternal (langsung pakai)
            // Jika tidak = path lokal (tambahkan '/' di depan)
            const imgSrc = resolveAssetUrl(product.image);

            // ============================================================
            // ISI HTML CARD PRODUK
            // ============================================================
            card.innerHTML = `
                <!-- Container gambar produk -->
                <div class="w-full aspect-square bg-gray-50 rounded-xl mb-4 flex items-center justify-center p-4 relative overflow-hidden">
                    ${badgeHtml}
                    <img src="${imgSrc}" alt="${product.name}" class="w-full h-full object-contain drop-shadow transition-transform duration-500 group-hover:scale-110">
                </div>
                <!-- Info produk -->
                <div class="flex flex-col flex-grow">
                    <!-- Label kategori -->
                    <div class="flex justify-center mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${categoryColor}">
                            <i class="fas ${categoryIcon} text-[8px]"></i> ${categoryLabel}
                        </span>
                    </div>
                    <!-- Nama produk -->
                    <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2 leading-tight">${product.name}</h3>
                    <!-- Deskripsi produk -->
                    <p class="text-gray-500 text-xs mb-3 line-clamp-2 h-8">${product.desc}</p>
                    <!-- Rating bintang -->
                    ${ratingHtml}
                    <!-- Harga -->
                    <div class="flex justify-center items-center mt-auto pt-4 border-t border-gray-50">
                        <span class="font-extrabold text-lg text-red-600">${formatRupiah(product.price)}</span>
                    </div>
                </div>`;

            // ============================================================
            // ANIMASI CARD MUNCUL (Fade-in + Slide-up)
            // ============================================================
            // Awalnya card tidak terlihat (opacity 0) dan sedikit ke bawah
            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";

            // Setelah delay (60ms x index), card akan muncul dengan animasi
            // Semakin ke bawah card-nya, semakin lama delay-nya (efek bertahap)
            setTimeout(() => {
                card.style.transition = "all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
                card.style.opacity = "1";
                card.style.transform = "translateY(0)";
            }, index * 60);

            // Tambahkan card ke dalam grid
            menuGrid.appendChild(card);
        });
    }

    // ====================================================================
    // EVENT LISTENER UNTUK TAB KATEGORI
    // ====================================================================

    // Ambil semua tombol tab kategori
    const tabs = document.querySelectorAll('.menu-category-tab');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Simpan kategori yang dipilih dari atribut data-category
            currentCategory = tab.dataset.category;

            // Update tampilan tab: hapus style aktif dari semua tab
            tabs.forEach(t => {
                t.classList.remove('bg-primary-red', 'text-white', 'active-tab');
                t.classList.add('bg-white', 'text-text-dark', 'border', 'border-gray-200');
            });

            // Berikan style aktif pada tab yang diklik
            tab.classList.add('bg-primary-red', 'text-white', 'active-tab');
            tab.classList.remove('bg-white', 'text-text-dark', 'border', 'border-gray-200');

            // Render ulang produk sesuai filter baru
            renderMenuProducts();
        });
    });

    // ====================================================================
    // INISIALISASI - Render produk pertama kali saat halaman dimuat
    // ====================================================================
    renderMenuProducts();
});
