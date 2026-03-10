<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi-Pok - Ledakan Kelezatan di Setiap Gigitan!</title>
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
                    backgroundImage: {
                        'menu-pattern': "url('/asset/bg menu.png')",
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        /* Hamburger → X animation */
        .hamburger-line {
            transition: all 0.3s cubic-bezier(.4,0,.2,1);
        }
        .hamburger-btn.active .hamburger-line:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        .hamburger-btn.active .hamburger-line:nth-child(2) {
            opacity: 0; transform: scaleX(0);
        }
        .hamburger-btn.active .hamburger-line:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        /* Mobile drawer */
        .mobile-drawer {
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(.4,0,.2,1);
        }
        .mobile-drawer.open {
            transform: translateX(0);
        }
        .drawer-overlay {
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .drawer-overlay.open {
            opacity: 1; pointer-events: auto;
        }
        /* Logo shrink on scroll */
        header.scrolled .header-logo {
            height: 50px !important;
        }
        @media (min-width: 768px) {
            header.scrolled .header-logo {
                height: 60px !important;
            }
        }
    </style>
</head>

<body class="font-body bg-bg-light text-text-dark leading-relaxed overflow-x-hidden">

    <!-- Header -->
    <header id="main-header" class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-sm shadow-md transition-all duration-300">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-2 md:py-3 flex justify-between items-center">

            <!-- Logo (Left) -->
            <a href="#home" class="flex-shrink-0 flex items-center">
                <img src="{{ asset('asset/logo merah.png') }}" alt="Chi-Pok Logo"
                    class="header-logo h-[60px] sm:h-[70px] md:h-[80px] lg:h-[90px] max-w-none object-contain z-10 transition-all duration-300 drop-shadow-sm">
            </a>

            <!-- Nav Links (Center) — hidden on mobile -->
            <nav class="hidden md:flex items-center">
                <ul class="nav-links flex gap-4 lg:gap-8 text-text-dark items-center">
                    <li><a href="#home"
                            class="font-heading text-base lg:text-xl hover:text-primary-red transition-colors duration-300 uppercase tracking-wide relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[2px] after:bg-primary-red after:transition-all after:duration-300 hover:after:w-full">HOME</a>
                    </li>
                    <li><a href="#menu"
                            class="font-heading text-base lg:text-xl hover:text-primary-red transition-colors duration-300 uppercase tracking-wide relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[2px] after:bg-primary-red after:transition-all after:duration-300 hover:after:w-full">MENU</a>
                    </li>
                    <li><a href="#contact"
                            class="font-heading text-base lg:text-xl hover:text-primary-red transition-colors duration-300 uppercase tracking-wide relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[2px] after:bg-primary-red after:transition-all after:duration-300 hover:after:w-full">CONTACT</a>
                    </li>
                    <li id="nav-admin" class="hidden"><a href="#" id="btn-admin-panel"
                            class="font-heading text-base lg:text-xl hover:text-primary-red transition-colors duration-300 uppercase tracking-wide">ADMIN</a>
                    </li>
                </ul>
            </nav>

            <!-- Action Icons (Right) -->
            <div class="flex items-center gap-2 sm:gap-3 md:gap-4">
                <!-- Cart Button -->
                <button id="btn-cart-header" class="relative text-lg sm:text-xl md:text-2xl text-primary-red hover:text-accent-red transition-colors p-1" title="Keranjang Belanja">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-badge" class="hidden absolute -top-1 -right-1 sm:-top-2 sm:-right-2 bg-red-600 text-white text-[8px] sm:text-[10px] font-bold w-4 h-4 sm:w-5 sm:h-5 rounded-full flex items-center justify-center animate-bounce">0</span>
                </button>
                <button id="btn-settings" class="hidden sm:block text-lg sm:text-xl md:text-2xl text-primary-red hover:text-accent-red transition-colors p-1"
                    title="Pengaturan">
                    <i class="fas fa-cog"></i>
                </button>
                <button id="btn-login" class="text-lg sm:text-xl md:text-2xl text-primary-red hover:text-accent-red transition-colors p-1"
                    title="Login / Logout">
                    <i class="fas fa-sign-in-alt"></i>
                </button>

                <!-- Hamburger Button (Mobile) -->
                <button class="hamburger-btn md:hidden flex flex-col justify-center items-center w-8 h-8 gap-[6px] ml-1 relative z-[60]" aria-label="Menu">
                    <span class="hamburger-line block w-6 h-[2px] bg-text-dark rounded-full origin-center"></span>
                    <span class="hamburger-line block w-6 h-[2px] bg-text-dark rounded-full origin-center"></span>
                    <span class="hamburger-line block w-6 h-[2px] bg-text-dark rounded-full origin-center"></span>
                </button>
            </div>

        </div>
    </header>

    <!-- Mobile Drawer Overlay -->
    <div class="drawer-overlay fixed inset-0 bg-black/50 z-[55] md:hidden"></div>

    <!-- Mobile Drawer -->
    <nav class="mobile-drawer fixed top-0 right-0 h-full w-[75%] max-w-[320px] bg-white z-[56] md:hidden shadow-2xl flex flex-col">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <img src="{{ asset('asset/logo merah.png') }}" alt="Chi-Pok" class="h-10 object-contain">
            <button class="drawer-close w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-500">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <!-- Drawer Links -->
        <ul class="nav-links-mobile flex flex-col py-4 flex-grow">
            <li><a href="#home" class="flex items-center gap-3 px-6 py-4 font-heading text-xl text-text-dark hover:bg-red-50 hover:text-primary-red transition-all duration-200 uppercase tracking-wide">
                <i class="fas fa-home text-base w-6 text-center text-gray-400"></i> HOME</a></li>
            <li><a href="#menu" class="flex items-center gap-3 px-6 py-4 font-heading text-xl text-text-dark hover:bg-red-50 hover:text-primary-red transition-all duration-200 uppercase tracking-wide">
                <i class="fas fa-utensils text-base w-6 text-center text-gray-400"></i> MENU</a></li>
            <li><a href="#contact" class="flex items-center gap-3 px-6 py-4 font-heading text-xl text-text-dark hover:bg-red-50 hover:text-primary-red transition-all duration-200 uppercase tracking-wide">
                <i class="fas fa-envelope text-base w-6 text-center text-gray-400"></i> CONTACT</a></li>
            <li id="nav-admin-mobile" class="hidden"><a href="#" id="btn-admin-panel-mobile" class="flex items-center gap-3 px-6 py-4 font-heading text-xl text-text-dark hover:bg-red-50 hover:text-primary-red transition-all duration-200 uppercase tracking-wide">
                <i class="fas fa-shield-alt text-base w-6 text-center text-gray-400"></i> ADMIN</a></li>
        </ul>
        <!-- Drawer Footer -->
        <div class="p-5 border-t border-gray-100">
            <div class="flex items-center justify-center gap-4">
                <button id="btn-settings-mobile" class="w-10 h-10 rounded-full bg-gray-100 text-primary-red flex items-center justify-center hover:bg-red-50 transition-colors" title="Pengaturan">
                    <i class="fas fa-cog"></i>
                </button>
                <button id="btn-login-mobile" class="flex-1 py-2.5 bg-gradient-to-r from-primary-red to-accent-red text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                    <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                </button>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Slider Section -->
        <section id="home" class="hero relative w-full overflow-hidden mt-[64px] sm:mt-[74px] md:mt-[86px]">

            <!-- Slider Container -->
            <div id="hero-slider" class="relative w-full h-[200px] sm:h-[300px] md:h-[400px] lg:h-[500px] xl:h-[600px]">
                <!-- Slide 1 -->
                <div class="hero-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out" style="opacity:1;">
                    <img src="{{ asset('asset/ledakan kelezatan.jpg') }}" alt="Ledakan Kelezatan"
                        class="w-full h-full object-cover block">
                </div>

                <!-- Slide 2 -->
                <div class="hero-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out" style="opacity:0;">
                    <img src="{{ asset('asset/vocer.jpg') }}" alt="Vocer Promo"
                        class="w-full h-full object-cover block">
                </div>
            </div>

            <!-- Slider Navigation Arrows -->
            <button id="hero-prev"
                class="absolute left-2 sm:left-4 md:left-8 top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/40 transition-all duration-300 hover:scale-110 flex items-center justify-center border border-white/30 hidden">
                <i class="fas fa-chevron-left text-sm sm:text-lg"></i>
            </button>
            <button id="hero-next"
                class="absolute right-2 sm:right-4 md:right-8 top-1/2 -translate-y-1/2 z-40 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/40 transition-all duration-300 hover:scale-110 flex items-center justify-center border border-white/30 hidden">
                <i class="fas fa-chevron-right text-sm sm:text-lg"></i>
            </button>

            <!-- Slider Dots -->
            <div id="hero-dots" class="absolute bottom-4 sm:bottom-8 left-1/2 -translate-x-1/2 z-40 flex gap-2 sm:gap-3 hidden">
            </div>

        </section>

        <!-- Menu Section -->
        <section id="menu" class="menu py-20 bg-bg-light bg-menu-pattern bg-cover bg-center">
            <div class="container mx-auto px-4 min-h-[70vh]">
                <h2
                    class="section-title font-heading text-5xl md:text-[3.5rem] text-primary-red text-center mb-4 uppercase pt-12">
                    MENU</h2>
                <p class="text-center text-text-grey mb-8 text-sm md:text-base">Pilih kategori favoritmu!</p>

                <!-- Category Tabs -->
                <div class="flex justify-center gap-3 mb-10" id="category-tabs">
                    <button data-category="semua"
                        class="category-tab active-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-primary-red text-white hover:shadow-lg hover:scale-105">
                        <i class="fas fa-utensils mr-1"></i> Semua
                    </button>
                    <button data-category="makanan"
                        class="category-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-white text-text-dark border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:scale-105">
                        <i class="fas fa-drumstick-bite mr-1"></i> Makanan
                    </button>
                    <button data-category="minuman"
                        class="category-tab px-6 py-2.5 rounded-full font-bold text-sm uppercase tracking-wide transition-all duration-300 shadow-md bg-white text-text-dark border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:scale-105">
                        <i class="fas fa-glass-water mr-1"></i> Minuman
                    </button>
                </div>

                <!-- Carousel Wrapper -->
                <div class="relative">
                    <!-- Prev Button -->
                    <button id="carousel-prev"
                        class="hidden absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-30 w-12 h-12 rounded-full bg-white shadow-lg border border-gray-200 text-primary-red hover:bg-primary-red hover:text-white transition-all duration-300 hover:scale-110 items-center justify-center">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <!-- Menu Grid / Carousel Track -->
                    <div class="overflow-hidden">
                        <div class="menu-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 transition-transform duration-500 ease-in-out"
                            id="menu-grid">
                            {{-- Products will be rendered by JavaScript --}}
                        </div>
                    </div>

                    <!-- Next Button -->
                    <button id="carousel-next"
                        class="hidden absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-30 w-12 h-12 rounded-full bg-white shadow-lg border border-gray-200 text-primary-red hover:bg-primary-red hover:text-white transition-all duration-300 hover:scale-110 items-center justify-center">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Carousel Dots -->
                <div id="carousel-dots" class="hidden flex justify-center gap-2 mt-6"></div>

                <!-- Lihat Semua Menu Link -->
                <div class="text-center mt-10">
                    <a href="/menu"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-full font-bold text-primary-red border-2 border-primary-red hover:bg-primary-red hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg text-sm uppercase tracking-wide group">
                        Lihat Semua Menu
                        <i class="fas fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact bg-[#B30000] py-20 text-white relative">
            <div class="container mx-auto px-4 contact-content relative h-[70vh]">
                <div
                    class="contact-header absolute top-0 right-0 hidden lg:block opacity-20 hover:opacity-100 transition-opacity duration-300">
                    <div class="footer-logo">
                        <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok Logo White"
                            class="h-[200px] brightness-0 invert mt-[-90px]">
                    </div>
                </div>

                <div class="contact-grid flex flex-col lg:flex-row justify-between gap-12 pt-10">
                    <div class="contact-left flex-1 min-w-[300px]">
                        <div class="social-icons flex gap-5 my-8">
                            <a href="#"
                                class="w-12 h-12 border-2 border-white rounded-full flex items-center justify-center text-xl transition-all duration-300 hover:bg-white hover:text-[#B30000] transform hover:scale-110"><i
                                    class="fab fa-instagram"></i></a>
                            <a href="#"
                                class="w-12 h-12 border-2 border-white rounded-full flex items-center justify-center text-xl transition-all duration-300 hover:bg-white hover:text-[#B30000] transform hover:scale-110"><i
                                    class="fab fa-whatsapp"></i></a>
                            <a href="#"
                                class="w-12 h-12 border-2 border-white rounded-full flex items-center justify-center text-xl transition-all duration-300 hover:bg-white hover:text-[#B30000] transform hover:scale-110"><i
                                    class="fab fa-tiktok"></i></a>
                        </div>

                        <div class="contact-details space-y-6">
                            <div class="detail-item">
                                <h4 class="font-heading text-xl tracking-wide mb-1">IKUTI KAMI</h4>
                                <p class="text-gray-100">Jl. Merdeka No. 123, Jakarta</p>
                            </div>
                            <div class="detail-item">
                                <h4 class="font-heading text-xl tracking-wide mb-1">ALAMAT OUTLET</h4>
                                <p class="text-gray-100"><i class="fas fa-map-marker-alt mr-2"></i> Setiap Hari, 10.00 -
                                    22.00</p>
                            </div>
                        </div>

                        <p class="copyright mt-12 text-sm opacity-80">© 2026 Chi Pok Indonesia. All Rights Reserved!</p>
                    </div>

                    <div class="contact-right flex-1 min-w-[300px]">
                        <h2 class="section-title text-white font-heading text-4xl mb-6 uppercase">CONTACT</h2>
                        <div
                            class="map-container rounded-[20px] overflow-hidden h-[300px] bg-gray-200 shadow-lg relative group">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126932.6288647893!2d106.75628659550778!3d-6.186933566160163!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Special%20Capital%20Region%20of%20Jakarta%2C%20Indonesia!5e0!3m2!1sen!2sus!4v1707505296053!5m2!1sen!2sus"
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="w-full h-full grayscale group-hover:grayscale-0 transition-all duration-500"></iframe>
                            <a href="https://maps.google.com" target="_blank"
                                class="btn btn-white absolute bottom-4 right-4 bg-white text-[#B30000] px-6 py-2 rounded-full font-bold shadow-md hover:bg-gray-100 transition-colors z-10 text-sm">BUKA
                                DI MAPS</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Cart / Checkout Modal -->
    <div id="cartModal" class="fixed inset-0 z-[2000] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="w-full">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-5 pb-3 border-b">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-shopping-cart text-red-600"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Keranjang Belanja</h3>
                            </div>
                            <button type="button" id="closeCartModal" class="text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <!-- Cart Items List -->
                        <div id="cart-items-container" class="max-h-[250px] overflow-y-auto space-y-3 mb-5">
                            <p class="text-gray-400 text-center py-8"><i class="fas fa-shopping-basket text-3xl mb-2 block"></i>Keranjang kosong</p>
                        </div>

                        <!-- Cart Total -->
                        <div class="bg-gray-50 rounded-xl p-4 mb-5">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-700">Total Belanja</span>
                                <span id="cart-total-display" class="text-xl font-extrabold text-red-600">Rp 0</span>
                            </div>
                        </div>

                        <!-- Checkout Form -->
                        <div id="cart-checkout-section" class="hidden">
                            <div class="border-t pt-4">
                                <h4 class="font-bold text-sm text-gray-900 mb-3 flex items-center gap-2">
                                    <i class="fas fa-truck text-red-500"></i> Detail Pengiriman
                                </h4>
                                <form id="checkoutForm" class="space-y-3">
                                    <div>
                                        <label for="checkout_jenis" class="block text-sm font-medium text-gray-700">Jenis Belanja</label>
                                        <select id="checkout_jenis" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                                            <option value="Dine In">Dine In (Makan di Tempat)</option>
                                            <option value="Take Away">Take Away (Bungkus)</option>
                                            <option value="Delivery">Delivery (Antar)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="checkout_nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                        <input type="text" id="checkout_nama" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                                    </div>
                                    <div>
                                        <label for="checkout_no_hp" class="block text-sm font-medium text-gray-700">No. HP / WhatsApp</label>
                                        <input type="tel" id="checkout_no_hp" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                        <div id="address-options" class="hidden mb-2">
                                            <label class="flex items-center gap-2 cursor-pointer bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm hover:bg-red-100 transition">
                                                <input type="checkbox" id="use_saved_address" class="accent-red-600">
                                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                                <span>Gunakan alamat tersimpan: <strong id="saved-address-preview" class="text-gray-800"></strong></span>
                                            </label>
                                        </div>
                                        <textarea id="checkout_alamat" rows="2" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2"
                                            placeholder="Masukkan alamat pengiriman"></textarea>
                                    </div>
                                    <div class="pt-2 flex gap-3">
                                        <button type="submit"
                                            class="flex-1 justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 transition flex items-center gap-2">
                                            <i class="fas fa-paper-plane"></i> Kirim Pesanan
                                        </button>
                                        <button type="button" id="btn-back-to-cart"
                                            class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
                                            Kembali
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Cart Action Buttons -->
                        <div id="cart-action-buttons" class="flex gap-3">
                            <button id="btn-checkout"
                                class="flex-1 justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                <i class="fas fa-credit-card"></i> Checkout
                            </button>
                            <button id="btn-clear-cart"
                                class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 transition">
                                <i class="fas fa-trash"></i> Kosongkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- LOGIN MODAL — Premium Split Layout                               -->
    <!-- ================================================================ -->
    <div id="loginModal" class="fixed inset-0 z-[2001] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Card -->
            <div class="relative w-full max-w-[900px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-500 animate-[modalIn_0.4s_ease-out]">

                <!-- Left — Branding Panel -->
                <div class="hidden md:flex md:w-[45%] bg-gradient-to-br from-[#D20000] via-[#B30000] to-[#8B0000] flex-col items-center justify-center p-10 relative overflow-hidden">
                    <!-- Decorative circles -->
                    <div class="absolute -top-20 -left-20 w-56 h-56 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-white/5 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-white/5 rounded-full"></div>

                    <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok Logo" class="h-28 brightness-0 invert mb-6 drop-shadow-2xl relative z-10">
                    <h2 class="font-heading text-3xl text-white text-center tracking-widest relative z-10 mb-3">SELAMAT DATANG</h2>
                    <p class="text-white/70 text-sm text-center max-w-[220px] relative z-10 leading-relaxed">Masuk ke akunmu dan nikmati ledakan kelezatan di setiap gigitan!</p>
                    <div class="mt-8 flex gap-3 relative z-10">
                        <span class="w-2 h-2 rounded-full bg-white/40"></span>
                        <span class="w-6 h-2 rounded-full bg-white"></span>
                        <span class="w-2 h-2 rounded-full bg-white/40"></span>
                    </div>
                </div>

                <!-- Right — Form Panel -->
                <div class="flex-1 p-8 md:p-12 flex flex-col justify-center">
                    <!-- Close button -->
                    <button type="button" id="closeLoginModal"
                        class="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 flex items-center justify-center transition-all duration-300 hover:rotate-90 z-20">
                        <i class="fas fa-times text-sm"></i>
                    </button>

                    <!-- Mobile logo -->
                    <div class="flex md:hidden justify-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#D20000] to-[#8B0000] rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                            <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok" class="h-10 brightness-0 invert">
                        </div>
                    </div>

                    <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">MASUK</h3>
                    <p class="text-gray-400 text-sm mb-8">Login ke akun Chi-Pok kamu</p>

                    <form id="loginForm" class="space-y-5">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-600 mb-1.5">Username</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <input type="text" id="username" name="username" required placeholder="Masukkan username"
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-600 mb-1.5">Password</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password" id="password" name="password" required placeholder="Masukkan password"
                                    class="w-full pl-11 pr-12 py-3 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                                <button type="button" onclick="togglePassword('password', this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-[#D20000] to-[#FF2E00] text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2 text-sm tracking-wide">
                            <i class="fas fa-sign-in-alt"></i> MASUK
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center gap-4 my-6">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-xs text-gray-400 font-medium">ATAU</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <p class="text-center text-sm text-gray-500">
                        Belum punya akun?
                        <a href="#" id="link-signup" class="text-red-600 font-bold hover:text-red-700 hover:underline transition-colors">Daftar disini</a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- SIGNUP MODAL — Premium Split Layout                              -->
    <!-- ================================================================ -->
    <div id="signupModal" class="fixed inset-0 z-[2002] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Card -->
            <div class="relative w-full max-w-[900px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-500 animate-[modalIn_0.4s_ease-out]">

                <!-- Left — Branding Panel -->
                <div class="hidden md:flex md:w-[45%] bg-gradient-to-br from-[#D20000] via-[#B30000] to-[#8B0000] flex-col items-center justify-center p-10 relative overflow-hidden">
                    <!-- Decorative circles -->
                    <div class="absolute -top-20 -left-20 w-56 h-56 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-white/5 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-white/5 rounded-full"></div>

                    <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok Logo" class="h-28 brightness-0 invert mb-6 drop-shadow-2xl relative z-10">
                    <h2 class="font-heading text-3xl text-white text-center tracking-widest relative z-10 mb-3">BERGABUNGLAH</h2>
                    <p class="text-white/70 text-sm text-center max-w-[220px] relative z-10 leading-relaxed">Buat akunmu sekarang dan jadi bagian dari keluarga Chi-Pok!</p>
                    <div class="mt-8 flex gap-3 relative z-10">
                        <span class="w-2 h-2 rounded-full bg-white/40"></span>
                        <span class="w-2 h-2 rounded-full bg-white/40"></span>
                        <span class="w-6 h-2 rounded-full bg-white"></span>
                    </div>
                </div>

                <!-- Right — Form Panel -->
                <div class="flex-1 p-8 md:p-10 flex flex-col justify-center">
                    <!-- Close button -->
                    <button type="button" id="closeSignupModal"
                        class="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 flex items-center justify-center transition-all duration-300 hover:rotate-90 z-20">
                        <i class="fas fa-times text-sm"></i>
                    </button>

                    <!-- Mobile logo -->
                    <div class="flex md:hidden justify-center mb-5">
                        <div class="w-14 h-14 bg-gradient-to-br from-[#D20000] to-[#8B0000] rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                            <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok" class="h-9 brightness-0 invert">
                        </div>
                    </div>

                    <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">DAFTAR</h3>
                    <p class="text-gray-400 text-sm mb-6">Buat akun baru di Chi-Pok</p>

                    <form id="signupForm" class="space-y-4">
                        <!-- Nama Lengkap -->
                        <div>
                            <label for="signup_name" class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-id-card text-sm"></i>
                                </span>
                                <input type="text" id="signup_name" name="signup_name" required placeholder="Nama lengkapmu"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="signup_username" class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <input type="text" id="signup_username" name="signup_username" required placeholder="Pilih username"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="signup_password" class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password" id="signup_password" name="signup_password" required placeholder="Buat password"
                                    class="w-full pl-11 pr-12 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                                <button type="button" onclick="togglePassword('signup_password', this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Two columns: No HP + Alamat -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="signup_no_hp" class="block text-sm font-semibold text-gray-600 mb-1">No. HP</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                        <i class="fas fa-phone text-sm"></i>
                                    </span>
                                    <input type="tel" id="signup_no_hp" name="signup_no_hp" placeholder="08xxxxxxxxxx"
                                        class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                                </div>
                            </div>
                            <div>
                                <label for="signup_alamat" class="block text-sm font-semibold text-gray-600 mb-1">Alamat</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-3 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                        <i class="fas fa-map-marker-alt text-sm"></i>
                                    </span>
                                    <textarea id="signup_alamat" name="signup_alamat" rows="1" placeholder="Alamat lengkap"
                                        class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300 resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Register Button -->
                        <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-[#D20000] to-[#FF2E00] text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2 text-sm tracking-wide mt-2">
                            <i class="fas fa-user-plus"></i> DAFTAR SEKARANG
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center gap-4 my-5">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-xs text-gray-400 font-medium">ATAU</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <p class="text-center text-sm text-gray-500">
                        Sudah punya akun?
                        <a href="#" id="link-login-from-signup" class="text-red-600 font-bold hover:text-red-700 hover:underline transition-colors">Masuk disini</a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Admin Panel Modal -->
    <div id="adminModal" class="fixed inset-0 z-[2003] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start flex-col w-full">
                        <div class="flex justify-between items-center w-full mb-6 border-b pb-4">
                            <h3 class="text-3xl font-heading text-red-600" id="modal-title">PANEL ADMIN</h3>
                            <button type="button" id="closeAdminModal"
                                class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div class="w-full mb-4">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <button id="tab-orders"
                                        class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm w-1/2 text-center hover:bg-red-50 transition-colors">
                                        Data Pesanan
                                    </button>
                                </nav>
                            </div>
                        </div>

                        <!-- Orders Content -->
                        <div id="content-orders" class="w-full">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Waktu</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Pelanggan</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Menu</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orders-table-body" class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum
                                                ada pesanan masuk.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Settings Content (Layout Mode) -->
                        <div id="content-settings" class="w-full hidden">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                                <h4 class="text-xl font-bold text-gray-900 mb-6">Pilih Mode Tampilan Menu</h4>
                                <div class="flex justify-center gap-8">
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="layout_mode" value="grid" class="hidden peer">
                                        <div
                                            class="w-40 h-32 border-2 border-gray-300 rounded-xl flex flex-col items-center justify-center gap-2 peer-checked:border-red-600 peer-checked:bg-red-50 hover:bg-gray-100 transition-all">
                                            <i class="fas fa-th-large text-3xl text-gray-400"></i>
                                            <span class="font-bold text-gray-500">Grid/Kotak</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="layout_mode" value="list" class="hidden peer">
                                        <div
                                            class="w-40 h-32 border-2 border-gray-300 rounded-xl flex flex-col items-center justify-center gap-2 peer-checked:border-red-600 peer-checked:bg-red-50 hover:bg-gray-100 transition-all">
                                            <i class="fas fa-list text-3xl text-gray-400"></i>
                                            <span class="font-bold text-gray-500">List/Daftar</span>
                                        </div>
                                    </label>
                                </div>
                                <p class="mt-6 text-sm text-gray-500">
                                    * Pengaturan ini akan mengubah tampilan menu untuk <b>Semua Pengunjung</b>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Floating Action Button (FAB) -->
    <button id="btn-add-menu-fab"
        class="fixed bottom-8 right-8 bg-red-600 text-white p-4 rounded-full shadow-lg hover:bg-red-700 transition-transform hover:scale-110 z-[1999] hidden flex items-center gap-2 group">
        <i class="fas fa-plus text-xl"></i>
        <span
            class="max-w-0 overflow-hidden group-hover:max-w-xs transition-all duration-300 ease-out whitespace-nowrap">Tambah
            Menu</span>
    </button>

    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 z-[2005] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="hidden sm:block mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-star text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">Ulasan Produk</h3>
                            <p class="text-sm text-gray-500 mb-4">Untuk <span id="review-product-name"
                                    class="font-bold text-gray-800"></span></p>

                            <!-- Existing Reviews List -->
                            <div id="existing-reviews" class="mb-6 max-h-60 overflow-y-auto space-y-3 text-left">
                            </div>

                            <div class="border-t pt-4">
                                <h4 class="font-bold text-sm text-gray-900 mb-2">Tulis Ulasan Baru</h4>
                                <form id="reviewForm" class="space-y-4">
                                    <input type="hidden" id="review_product_id">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Rating</label>
                                        <div class="flex gap-2 mt-1 justify-center sm:justify-start"
                                            id="star-rating-input">
                                            <i class="far fa-star text-2xl text-yellow-400 cursor-pointer hover:scale-110 transition"
                                                data-value="1"></i>
                                            <i class="far fa-star text-2xl text-yellow-400 cursor-pointer hover:scale-110 transition"
                                                data-value="2"></i>
                                            <i class="far fa-star text-2xl text-yellow-400 cursor-pointer hover:scale-110 transition"
                                                data-value="3"></i>
                                            <i class="far fa-star text-2xl text-yellow-400 cursor-pointer hover:scale-110 transition"
                                                data-value="4"></i>
                                            <i class="far fa-star text-2xl text-yellow-400 cursor-pointer hover:scale-110 transition"
                                                data-value="5"></i>
                                        </div>
                                        <input type="hidden" id="review_rating" required>
                                    </div>
                                    <div>
                                        <label for="review_comment"
                                            class="block text-sm font-medium text-gray-700">Komentar</label>
                                        <textarea id="review_comment" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2"
                                            placeholder="Ceritakan pengalamanmu..."></textarea>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit"
                                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Kirim
                                            Ulasan</button>
                                        <button type="button" id="closeReviewModal"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Tutup</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Modal Add Menu -->
    <div id="addMenuModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <h3 class="text-xl font-bold mb-4">Tambah Menu Baru</h3>
                    <form id="addMenuForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Menu</label>
                            <input type="text" id="new_menu_name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                            <input type="number" id="new_menu_price" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea id="new_menu_desc" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">URL Gambar (Opsional)</label>
                            <input type="text" id="new_menu_img" placeholder="https://..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori</label>
                            <select id="new_menu_category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
                                <option value="makanan">🍗 Makanan</option>
                                <option value="minuman">🥤 Minuman</option>
                            </select>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:col-start-2">Tambah</button>
                            <button type="button" id="closeAddMenuModal"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Pass products data from Laravel to JavaScript --}}
    <script>
        const PRODUCTS_DATA = @json($productsData);
        const CSRF_TOKEN = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/hero-slider.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Link: "Masuk disini" from signup modal
        document.getElementById('link-login-from-signup')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('signupModal').classList.add('hidden');
            document.getElementById('loginModal').classList.remove('hidden');
        });
    </script>
</body>

</html>
