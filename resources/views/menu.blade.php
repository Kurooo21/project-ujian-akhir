<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Lengkap - Chi-Pok</title>
    <!-- Tailwind CSS CDN -->
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
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="font-body bg-bg-light text-text-dark leading-relaxed">

    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('asset/logo merah.png') }}" alt="Chi-Pok Logo"
                    class="h-14 object-contain transition-transform group-hover:scale-105">
                <span class="font-heading text-2xl text-primary-red uppercase tracking-wide">Chi-Pok</span>
            </a>
            <a href="/"
                class="inline-flex items-center gap-2 px-6 py-2 rounded-full font-bold text-sm text-primary-red border-2 border-primary-red hover:bg-primary-red hover:text-white transition-all duration-300 hover:scale-105">
                <i class="fas fa-arrow-left"></i> Kembali ke Home
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 min-h-screen">
        <!-- Page Title -->
        <div class="text-center mb-10">
            <h1 class="font-heading text-5xl md:text-6xl text-primary-red uppercase mb-3">Menu Lengkap</h1>
            <p class="text-text-grey text-sm md:text-base">Temukan semua pilihan makanan & minuman favoritmu!</p>
        </div>

        <!-- Category Filter Tabs -->
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

        <!-- Product Count -->
        <p id="product-count" class="text-center text-sm text-text-grey mb-6"></p>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8" id="full-menu-grid">
            {{-- Products rendered by JS --}}
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary-red text-white py-8 text-center">
        <p class="text-sm opacity-80">© 2026 Chi Pok Indonesia. All Rights Reserved!</p>
    </footer>

    {{-- Pass products data from Laravel to JavaScript --}}
    <script>
        const PRODUCTS_DATA = @json($productsData);
    </script>
    <script src="{{ asset('js/menu-page.js') }}"></script>
</body>

</html>
