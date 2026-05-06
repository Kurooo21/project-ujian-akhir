@extends('layouts.frontend')
@section('title', 'Semua Menu - Chi-Pok')

@push('styles')
<style>
    #main-header {
        display: none !important;
    }

    main {
        margin-top: 0 !important;
        background: transparent !important;
    }

    .menu-shell {
        background:
            

        
    }

    .menu-shell::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.55) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.55) 1px, transparent 1px);
        background-size: 28px 28px;
        opacity: 0.18;
        pointer-events: none;
    }

</style>
@endpush

@section('content')
<section class="menu-shell relative min-h-screen overflow-hidden">
    <div class="relative z-10 px-4 py-5 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-md shadow-red-100/40 backdrop-blur transition hover:-translate-x-0.5 hover:border-red-200 hover:text-red-600">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Kembali</span>
            </a>

            @auth
                <a href="{{ route('checkout') }}" id="menu-cart-link"
                    class="inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-[#D20000] via-[#E11D24] to-[#F97316] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                        <i class="fas fa-shopping-cart text-sm"></i>
                    </span>
                    <span>Keranjang</span>
                    <span id="menu-cart-count"
                        class="hidden min-w-[1.75rem] rounded-full bg-white px-2 py-1 text-center text-xs font-black text-red-600">0</span>
                </a>
            @else
                <button type="button" id="menu-cart-link" onclick="showLoginPopup()"
                    class="inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-[#D20000] via-[#E11D24] to-[#F97316] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                        <i class="fas fa-shopping-cart text-sm"></i>
                    </span>
                    <span>Keranjang</span>
                    <span id="menu-cart-count"
                        class="hidden min-w-[1.75rem] rounded-full bg-white px-2 py-1 text-center text-xs font-black text-red-600">0</span>
                </button>
            @endauth
        </div>

        <div class="mx-auto max-w-7xl pb-16 pt-10">
            <div class="mb-7 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div id="product-count"
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                    <i class="fas fa-bowl-food text-red-500"></i>
                    Menampilkan 0 menu
                </div>

                <div class="flex flex-wrap gap-3" id="category-tabs">
                    <button type="button" data-category="semua"
                        class="menu-category-tab active-tab inline-flex items-center gap-2 rounded-full bg-primary-red px-5 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-md shadow-red-200 transition hover:-translate-y-0.5 hover:shadow-lg">
                        <i class="fas fa-utensils text-xs"></i>
                        Semua
                    </button>
                    <button type="button" data-category="makanan"
                        class="menu-category-tab inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:text-red-600">
                        <i class="fas fa-drumstick-bite text-xs"></i>
                        Makanan
                    </button>
                    <button type="button" data-category="minuman"
                        class="menu-category-tab inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:text-red-600">
                        <i class="fas fa-glass-water text-xs"></i>
                        Minuman
                    </button>
                </div>
            </div>

            <div id="full-menu-grid" class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4"></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const PRODUCTS_DATA = @json($productsData);
    const APP_BASE_URL = @json(rtrim(url('/'), '/'));
    const LOGIN_URL = @json(route('login'));
    const CHECKOUT_URL = @json(route('checkout'));
    const CURRENT_USER_DATA = @json(Auth::check() ? ['id' => Auth::id(), 'name' => Auth::user()->name] : null);
</script>
<script src="{{ asset('js/menu-page.js') }}?v={{ time() }}"></script>
@endpush
