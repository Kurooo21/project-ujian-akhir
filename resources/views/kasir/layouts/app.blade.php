{{--
    ============================================================
    LAYOUT UTAMA KASIR — resources/views/kasir/layouts/app.blade.php
    ============================================================
    File ini adalah KERANGKA (layout) yang digunakan oleh semua
    halaman di panel Kasir, seperti: Dashboard, Pesanan, dll.

    Cara kerjanya:
    - Halaman lain (misal: dashboard.blade.php) menulis:
        @extends('kasir.layouts.app')
    - Lalu mengisi konten ke dalam slot @yield('content')
    - Judul halaman diisi via @yield('page_title')
    - Script tambahan dimasukkan via @push('scripts')

    Struktur halaman:
    ┌──────────────────────────────────────────┐
    │  [Sidebar - Menu Navigasi Kiri]          │
    │  ┌────────────────────────────────────┐  │
    │  │ [Header - Judul + Tombol Logout]   │  │
    │  ├────────────────────────────────────┤  │
    │  │ [Alert Flash Messages]             │  │
    │  │ [Konten Halaman - @yield('content')]│  │
    │  └────────────────────────────────────┘  │
    └──────────────────────────────────────────┘
    ============================================================
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    {{-- Encoding karakter standar untuk mendukung huruf Indonesia --}}
    <meta charset="UTF-8">

    {{-- Agar tampilan responsif di layar HP/tablet --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF Token: token keamanan untuk setiap form/request POST --}}
    {{-- JavaScript bisa membaca ini via: document.querySelector('meta[name="csrf-token"]') --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kasir Panel - Chi-Pok</title>

    {{-- Google Fonts: font Inter agar tampilan lebih bersih & profesional --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS via CDN: framework CSS untuk mempercepat styling --}}
    {{-- Di production sebaiknya di-compile, tapi untuk development CDN sudah cukup --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind: atur font default ke "Inter"
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'], // Override font default browser
                    }
                }
            }
        }
    </script>

    {{-- SweetAlert2: library untuk pop-up notifikasi yang cantik --}}
    {{-- Digunakan di handleLogout() dan halaman kasir lainnya --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /*
         * Kustomisasi scrollbar untuk sidebar
         * Agar scrollbar di sidebar terlihat tipis dan tidak mengganggu tampilan
         */
        .sidebar-scroll::-webkit-scrollbar        { width: 4px; }          /* Lebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar-track  { background: transparent; } /* Track transparan */
        .sidebar-scroll::-webkit-scrollbar-thumb  { background: #334155; border-radius: 99px; } /* Thumb gelap */

        /*
         * Pastikan SweetAlert2 selalu muncul di atas semua elemen lain
         * z-index harus lebih tinggi dari sidebar (z-[1000]) dan overlay (z-[999])
         */
        .swal2-container { z-index: 99999 !important; }
    </style>
</head>

{{-- Body: background putih bersih, antialiased agar font lebih halus --}}
{{-- "flex" agar sidebar dan konten utama bisa berdampingan secara horizontal --}}
<body class="bg-white text-slate-900 font-sans min-h-screen antialiased flex">

    {{-- ============================================================
         OVERLAY GELAP (Mobile Only)
         ============================================================
         Lapisan hitam semi-transparan yang muncul di belakang sidebar
         saat sidebar dibuka di layar kecil (HP/tablet).
         Diklik → memanggil closeSidebar() untuk menutup sidebar.
         "lg:hidden" = hanya muncul di layar < 1024px (mobile/tablet)
         ============================================================ --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] hidden transition-opacity lg:hidden"
         id="sidebarOverlay"
         onclick="closeSidebar()">
    </div>

    {{-- ============================================================
         SIDEBAR — Menu Navigasi Kiri
         ============================================================
         Sidebar berwarna merah gelap (brand Chi-Pok) dengan lebar 260px.
         - Di layar besar (lg+): selalu terlihat (translate-x-0)
         - Di layar kecil: tersembunyi ke kiri (-translate-x-full)
           dan bisa dibuka dengan tombol hamburger di header
         ============================================================ --}}
    <aside class="fixed inset-y-0 left-0 bg-red-900 text-white w-[260px] flex flex-col z-[1000] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
           id="sidebar">

        {{-- ── Logo & Nama Panel ─────────────────────────────── --}}
        <div>
            {{-- Logo Chi-Pok (putih) --}}
            <img src="{{ asset('asset/logo putih.png') }}" alt="Logo" class="w-15 h-15">
            <div>
                <span class="text-[15px] font-bold text-white uppercase tracking-widest ml-16">Kasir Panel</span>
            </div>
        </div>

        {{-- ── Daftar Menu Navigasi ──────────────────────────── --}}
        {{-- overflow-y-auto: menu bisa di-scroll jika banyak item --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto sidebar-scroll">

            {{-- Label grup menu --}}
            <div class="text-[13px] font-semibold text-white uppercase tracking-widest px-3 pt-4 pb-2">Menu Kasir</div>

            {{-- Ambil nama route halaman yang sedang aktif --}}
            {{-- Digunakan untuk memberi highlight pada menu yang sedang dibuka --}}
            @php
                $currentRoute = Route::currentRouteName();
            @endphp

            {{-- ── Menu 1: Dashboard ────────────────────────── --}}
            {{-- Jika halaman aktif = 'kasir.dashboard' → tampilkan putih (aktif) --}}
            {{-- Jika tidak → tampilkan abu-abu dengan efek hover putih --}}
            <a href="{{ route('kasir.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all mb-1
                      {{ $currentRoute == 'kasir.dashboard'
                           ? 'bg-white text-black shadow-[0_2px_8px_rgba(37,99,235,0.35)]'
                           : 'text-slate-300 hover:bg-white hover:text-black' }}">
                {{-- Icon: grid/kotak (dashboard) --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 flex-shrink-0">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            {{-- ── Menu 2: Pesanan Outlet ───────────────────── --}}
            {{-- Halaman untuk melihat & memproses pesanan masuk --}}
            <a href="{{ route('kasir.pesanan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all mb-1
                      {{ $currentRoute == 'kasir.pesanan'
                           ? 'bg-white text-black shadow-[0_2px_8px_rgba(37,99,235,0.35)]'
                           : 'text-slate-300 hover:bg-white hover:text-black' }}">
                {{-- Icon: tas belanja (pesanan) --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 flex-shrink-0">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                Pesanan Outlet
            </a>
        </nav>

        {{-- ── Info Kasir yang Sedang Login ─────────────────── --}}
        {{-- Ditampilkan di bagian bawah sidebar sebagai identitas kasir --}}
        <div class="p-4 border-t border-white/20">
            <div class="flex items-center gap-3 px-3 py-2 rounded-md">
                {{-- Avatar: logo merah dalam lingkaran putih --}}
                <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center font-bold text-sm">
                    <img src="{{ asset('asset/logo merah.png') }}" alt="Logo" class="w-10 h-13">
                </div>
                <div>
                    {{-- Nama kasir yang login. Jika kosong, tampilkan "Kasir" --}}
                    <h4 class="text-[13px] font-semibold">{{ Auth::user()->name ?? 'Kasir' }}</h4>
                    {{-- Nama outlet yang ditugaskan ke kasir ini --}}
                    {{-- "?->" = optional chaining: aman jika relasi outlet belum ada --}}
                    <span class="text-[11px] text-white/60">{{ Auth::user()->outlet?->name ?? 'Outlet belum diatur' }}</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- ============================================================
         KONTEN UTAMA — Area di sebelah kanan sidebar
         ============================================================
         "lg:ml-[260px]" = beri margin kiri 260px di layar besar
         agar konten tidak tertimpa sidebar yang fixed
         ============================================================ --}}
    <main class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-[260px]">

        {{-- ──────────────────────────────────────────────────────
             HEADER / TOPBAR
             ──────────────────────────────────────────────────────
             Bar atas yang menampilkan:
             - Kiri: Tombol hamburger (mobile) + Judul halaman + Tanggal
             - Kanan: Tombol Logout
             "sticky top-0" = tetap di atas saat halaman di-scroll
             "backdrop-blur-md" = efek kaca buram saat konten scroll ke bawahnya
             ────────────────────────────────────────────────────── --}}
        <header class="sticky top-0 h-16 bg-white/85 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 lg:px-7 z-[500]">

            <div class="flex items-center gap-4">
                {{-- Tombol Hamburger: hanya muncul di layar kecil (mobile/tablet) --}}
                {{-- Diklik → membuka sidebar via openSidebar() --}}
                <button class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-md transition"
                        onclick="openSidebar()"
                        aria-label="Toggle sidebar">
                    {{-- Icon: tiga garis horizontal (hamburger menu) --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <div>
                    {{-- Judul halaman: diisi oleh masing-masing halaman via @section('page_title') --}}
                    {{-- Default: "Dashboard Kasir" jika tidak diisi --}}
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard Kasir')</h1>

                    {{-- Tanggal hari ini dalam Bahasa Indonesia (misal: Selasa, 29 April 2026) --}}
                    {{-- translatedFormat() = Carbon method untuk format tanggal terlocalisasi --}}
                    <p class="text-[13px] text-slate-400 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            {{-- Tombol Logout di pojok kanan header --}}
            <div class="flex items-center gap-2.5">
                <button type="button"
                        class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-md text-[13px] font-medium transition"
                        onclick="handleLogout()">
                    {{-- Icon: panah keluar (logout) --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    {{-- "hidden sm:inline": teks hanya muncul di layar ≥ 640px --}}
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </div>
        </header>

        {{-- ──────────────────────────────────────────────────────
             AREA KONTEN HALAMAN
             ────────────────────────────────────────────────────── --}}
        <div class="p-4 lg:p-7">

            {{-- ── Flash Message: Sukses ─────────────────────── --}}
            {{-- Muncul jika ada session('success') dari controller --}}
            {{-- Contoh: setelah berhasil konfirmasi pembayaran --}}
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ── Flash Message: Error ──────────────────────── --}}
            {{-- Muncul jika ada session('error') dari controller --}}
            @if(session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ── Flash Message: Validation Errors ────────────── --}}
            {{-- Muncul jika Laravel menemukan error validasi form --}}
            {{-- $errors->any() = true jika ada minimal 1 error --}}
            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    <div class="font-semibold mb-1">Ada data yang perlu dicek lagi:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        {{-- Tampilkan semua pesan error satu per satu --}}
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Slot Konten Utama ─────────────────────────── --}}
            {{-- Diisi oleh halaman child (dashboard.blade.php, pesanan.blade.php, dll) --}}
            {{-- via @section('content') ... @endsection --}}
            @yield('content')
        </div>
    </main>

    {{-- ============================================================
         JAVASCRIPT: Kontrol Sidebar & Logout
         ============================================================ --}}
    <script>
        // Ambil referensi elemen sidebar dan overlay dari DOM
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        /**
         * openSidebar() — Buka sidebar di tampilan mobile
         * Dipanggil saat tombol hamburger (☰) diklik di header
         * - Hapus class -translate-x-full agar sidebar geser ke kanan (muncul)
         * - Tampilkan overlay gelap di belakang sidebar
         * - Kunci scroll body agar halaman tidak ikut bergulir
         */
        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Kunci scroll halaman
        }

        /**
         * closeSidebar() — Tutup sidebar di tampilan mobile
         * Dipanggil saat overlay diklik atau tombol close ditekan
         * - Tambahkan -translate-x-full agar sidebar geser ke kiri (sembunyi)
         * - Sembunyikan overlay
         * - Buka kembali scroll body
         */
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = ''; // Buka kembali scroll halaman
        }

        /**
         * handleLogout() — Proses logout kasir
         * Menggunakan fetch (AJAX) untuk mengirim POST ke route logout Laravel.
         * Setelah logout berhasil (atau gagal), user diarahkan ke halaman utama (/).
         *
         * Kenapa pakai fetch dan bukan form biasa?
         * → Agar proses logout bisa dilakukan dari tombol tanpa perlu tag <form>
         *   dan tetap aman karena menyertakan CSRF token di header.
         */
        async function handleLogout() {
            try {
                // Ambil CSRF token dari meta tag di <head>
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Kirim POST request ke route 'logout' Laravel
                await fetch('{{ route("logout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken, // Wajib ada untuk request POST di Laravel
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin', // Sertakan cookie session
                });

                // Arahkan user ke halaman utama setelah logout
                window.location.href = '{{ url("/") }}';
            } catch (err) {
                // Jika fetch gagal (misal: jaringan bermasalah), tetap arahkan ke halaman utama
                window.location.href = '{{ url("/") }}';
            }
        }
    </script>

    {{-- ============================================================
         @stack('scripts')
         ============================================================
         Slot untuk script tambahan dari halaman child.
         Halaman lain bisa menambahkan script spesifik via:
             @push('scripts')
                 <script> ... </script>
             @endpush
         Contoh: halaman pesanan.blade.php menggunakan ini untuk
         memuat order-detail-scripts.blade.php
         ============================================================ --}}
    @stack('scripts')
</body>
</html>
