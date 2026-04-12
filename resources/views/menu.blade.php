<!-- ================================================================
     MENU.BLADE.PHP - Halaman Menu Lengkap Chi-Pok
     ================================================================
     File ini adalah HALAMAN MENU TERPISAH (bukan di halaman utama).
     Menampilkan SEMUA produk dalam grid penuh dengan filter kategori.

     STRUKTUR HALAMAN:
     1. HEAD: Meta tags, Tailwind CSS, Google Fonts, Font Awesome
     2. HEADER: Logo + Tombol kembali ke Home
     3. MAIN: Judul, Tab filter, Counter produk, Grid produk
     4. FOOTER: Copyright
     5. SCRIPTS: Data produk + menu-page.js
     ================================================================ -->
<!DOCTYPE html>
<!-- lang="id" = bahasa Indonesia, scroll-smooth = animasi scroll halus -->
<html lang="id" class="scroll-smooth">

<!-- ================================================================
     BAGIAN HEAD - Metadata & Resource Eksternal
     ================================================================ -->
<head>
    <!-- Encoding karakter UTF-8 -->
    <meta charset="UTF-8">
    <!-- Viewport agar responsif di semua ukuran layar -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token untuk keamanan AJAX request -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Lengkap - Chi-Pok</title>

    <!-- Tailwind CSS - Framework CSS berbasis class -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Konfigurasi Tailwind: warna & font kustom (sama seperti home.blade.php) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-red': '#D20000',      // Merah utama
                        'accent-red': '#FF2E00',       // Merah aksen
                        'primary-white': '#FFFFFF',    // Putih
                        'text-dark': '#333333',        // Teks gelap
                        'text-grey': '#666666',        // Teks abu-abu
                        'bg-light': '#F9F9F9',         // Background terang
                        'mustard': '#FFC107',          // Kuning mustard (rating)
                    },
                    fontFamily: {
                        heading: ['Anton', 'sans-serif'],     // Font judul
                        body: ['Poppins', 'sans-serif'],      // Font body
                    },
                }
            }
        }
    </script>
    <!-- Google Fonts: Anton (judul) & Poppins (body text) -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome: Library icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2: Pop-up modern nan cantik -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<!-- ================================================================
     BODY - Isi halaman menu
     ================================================================ -->
<body class="font-body bg-bg-light text-text-dark leading-relaxed">

    <!-- ============================================================
         HEADER - Navigasi sederhana (Logo + Tombol Kembali)
         ============================================================
         sticky top-0 = tetap di atas saat di-scroll
         Berbeda dengan home.blade.php, header ini lebih simpel
         karena halaman ini hanya untuk menampilkan menu
         ============================================================ -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <!-- Logo + Nama Brand (klik untuk kembali ke home) -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('asset/logo merah.png') }}" alt="Chi-Pok Logo"
                    class="h-14 object-contain transition-transform group-hover:scale-105">
                <span class="font-heading text-2xl text-primary-red uppercase tracking-wide">Chi-Pok</span>
            </a>
            <!-- Tombol "Kembali ke Home" -->
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 px-6 py-2 rounded-full font-bold text-sm text-primary-red border-2 border-primary-red hover:bg-primary-red hover:text-white transition-all duration-300 hover:scale-105">
                <i class="fas fa-arrow-left"></i> Kembali ke Home
            </a>
        </div>
    </header>

    <!-- ============================================================
         KONTEN UTAMA - Grid Produk dengan Filter
         ============================================================ -->
    <main class="container mx-auto px-4 py-10 min-h-screen">
        <!-- Judul Halaman -->
        <div class="text-center mb-10">
            <h1 class="font-heading text-5xl md:text-6xl text-primary-red uppercase mb-3">Menu Lengkap</h1>
            <p class="text-text-grey text-sm md:text-base">Temukan semua pilihan makanan & minuman favoritmu!</p>
        </div>

        <!-- Tab Filter Kategori -->
        <!-- data-category = value kategori untuk filtering oleh JavaScript (menu-page.js) -->
        <div class="flex justify-center gap-3 mb-10" id="menu-category-tabs">
            <button data-category="semua"
                class="menu-category-tab active-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-primary-red text-white hover:shadow-lg hover:scale-105">
                <i class="fas fa-utensils mr-1"></i> Semua
            </button>
            <button data-category="makanan"
                class="menu-category-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-white text-text-dark border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:scale-105">
                <i class="fas fa-drumstick-bite mr-1"></i> Makanan
            </button>
            <button data-category="minuman"
                class="menu-category-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-white text-text-dark border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:scale-105">
                <i class="fas fa-glass-water mr-1"></i> Minuman
            </button>
        </div>

        <!-- Counter jumlah produk yang ditampilkan (diisi oleh JavaScript) -->
        <p id="product-count" class="text-center text-sm text-text-grey mb-6"></p>

        <!-- Grid Menu - Tempat card produk di-render oleh JavaScript (menu-page.js) -->
        <!-- Responsive: 1 kolom di HP, 2 di tablet, 3 di laptop, 4 di desktop -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8" id="full-menu-grid">
            {{-- Produk akan di-render oleh JavaScript (menu-page.js) --}}
        </div>
    </main>

    <!-- Footer sederhana -->
    <footer class="bg-primary-red text-white py-8 text-center">
        <p class="text-sm opacity-80">© 2026 Chi Pok Indonesia. All Rights Reserved!</p>
    </footer>

    <!-- ============================================================
         SCRIPT SECTION
         ============================================================ -->

    {{-- Meneruskan data produk dari Laravel (PHP) ke JavaScript --}}
    {{-- @json() mengkonversi array PHP menjadi JSON untuk diakses oleh JS --}}
    <script>
        const PRODUCTS_DATA = @json($productsData);                 // Data produk dari database
        const APP_BASE_URL = @json(rtrim(url('/'), '/'));           // Base URL aplikasi (support subfolder)
    </script>
    <!-- File JavaScript untuk halaman menu (rendering produk, filter, animasi) -->
    <script src="{{ asset('js/menu-page.js') }}"></script>
</body>

</html>
