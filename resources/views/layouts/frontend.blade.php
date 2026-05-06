<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chi-Pok - Ledakan Kelezatan di Setiap Gigitan!')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-red': '#D20000',
                        'accent-red': '#FF2E00',
                        'primary-white': '#FFFFFF',
                        'text-dark': '#333333',
                        'text-grey': '#666666',
                        'bg-light': '#F9F9F9',
                        'mustard': '#FFC107',
                    },
                    fontFamily: {
                        heading: ['Anton', 'sans-serif'],
                        body: ['Poppins', 'sans-serif'],
                    },
                    backgroundImage: {
                        'menu-pattern': "url('{{ asset('asset/bg menu.jpg') }}')",
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .swal2-container { z-index: 99999 !important; }
        .hamburger-line { transition: all 0.3s cubic-bezier(.4,0,.2,1); }
        .hamburger-btn.active .hamburger-line:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .hamburger-btn.active .hamburger-line:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .hamburger-btn.active .hamburger-line:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        .mobile-drawer { transform: translateX(100%); transition: transform 0.35s cubic-bezier(.4,0,.2,1); }
        .mobile-drawer.open { transform: translateX(0); }
        .drawer-overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .drawer-overlay.open { opacity: 1; pointer-events: auto; }
    </style>
    @stack('styles')
</head>
<body class="font-body bg-bg-light text-text-dark leading-relaxed overflow-x-hidden">
    <header id="main-header" class="fixed top-0 left-0 w-full z-50 bg-white shadow-md">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-2 md:py-3 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('asset/logo merah.png') }}" alt="Chi-Pok Logo" class="h-12 sm:h-14 md:h-16 object-contain">
            </a>

            <nav class="hidden md:flex items-center">
                <ul class="flex gap-4 lg:gap-8 text-text-dark items-center">
                    <li><a href="{{ route('home') }}#home" class="font-heading text-base hover:text-primary-red transition-colors">HOME</a></li>
                    <li><a href="{{ route('home') }}#menu" class="font-heading text-base hover:text-primary-red transition-colors">MENU</a></li>
                    <li><a href="{{ route('home') }}#contact" class="font-heading text-base hover:text-primary-red transition-colors">CONTACT</a></li>
                </ul>
            </nav>

            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Tombol Keranjang --}}
                @auth
                    {{-- Sudah login → langsung ke halaman checkout --}}
                    <a href="{{ route('checkout') }}" class="relative text-lg sm:text-xl text-primary-red hover:text-accent-red p-1" title="Keranjang">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                @else
                    {{-- Belum login → tampilkan popup SweetAlert agar user login dulu --}}
                    <button type="button" onclick="showLoginPopup()" class="relative text-lg sm:text-xl text-primary-red hover:text-accent-red p-1" title="Keranjang">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                @endauth
                @auth
                    <a href="{{ route('user.settings') }}" class="hidden sm:block text-lg sm:text-xl text-primary-red hover:text-accent-red p-1" title="Pengaturan">
                        <i class="fas fa-cog"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-lg sm:text-xl text-primary-red hover:text-accent-red p-1" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-lg sm:text-xl text-primary-red hover:text-accent-red p-1" title="Login">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mt-[80px] min-h-screen">
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        /**
         * showLoginPopup() - Tampilkan popup SweetAlert jika user belum login
         * dan mencoba mengakses fitur yang membutuhkan autentikasi (misalnya keranjang)
         */
        function showLoginPopup() {
            Swal.fire({
                title: 'Login Dulu untuk Membuka Keranjang',
                html: 'Masuk ke akunmu dulu ya supaya <b>keranjang belanja</b> dan proses pesan menu bisa berjalan dengan lancar.',
                icon: 'info',
                iconColor: '#D20000',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sign-in-alt mr-1"></i> Login Sekarang',
                cancelButtonText: 'Nanti Dulu',
                confirmButtonColor: '#D20000',
                cancelButtonColor: '#6b7280',
                reverseButtons: false,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl font-bold',
                    cancelButton: 'rounded-xl font-bold',
                }
            }).then((result) => {
                // Jika user klik tombol "Login Sekarang" → arahkan ke halaman login
                if (result.isConfirmed) {
                    window.location.href = '{{ route("login") }}';
                }
            });
        }
    </script>
</body>
</html>
