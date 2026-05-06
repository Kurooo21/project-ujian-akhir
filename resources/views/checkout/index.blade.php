@extends('layouts.frontend')
@section('title', 'Keranjang & Checkout - Chi-Pok')

@push('styles')
<style>
    #main-header {
        display: none !important;
    }

    main {
        margin-top: 0 !important;
        background: transparent !important;
    }

    .checkout-shell {
        background:
            radial-gradient(circle at top left, rgba(148, 163, 184, 0.12), transparent 28%),
            radial-gradient(circle at bottom right, rgba(226, 232, 240, 0.7), transparent 26%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 48%, #f8fafc 100%);
    }
</style>
@endpush

@section('content')
<section class="checkout-shell min-h-screen px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-6xl items-center gap-3">
        <a href="{{ route('menu') }}"
            class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:border-red-300 hover:bg-red-50 hover:text-red-900">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Lanjut Belanja</span>
        </a>
    </div>

    <div class="mx-auto max-w-6xl pb-16 pt-10">
        <div class="mb-8 grid gap-4 lg:grid-cols-[1.2fr,0.8fr]">
            <div class="rounded-[28px] border border-slate-200/80 bg-white/95 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.05)] backdrop-blur sm:p-8">
                <div class="flex flex-col gap-5 border-b border-slate-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-red-100 bg-red-50 text-red-600">
                            <i class="fas fa-shopping-cart text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-red-300">Keranjang Belanja</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-red-900 sm:text-[2.15rem]">
                                Keranjangmu
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-red-700">
                                Cek item yang dipilih, atur jumlahnya, lalu lanjutkan checkout kalau sudah siap.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('user.orders.page') }}" id="btn-show-user-orders"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-50 hover:text-red-900">
                        <i class="fas fa-receipt"></i>
                        <span>Riwayat Pesanan</span>
                    </a>
                </div>

                <div class="mt-6 rounded-[26px] border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-300">Daftar Item</p>
                            <p class="mt-1 text-sm font-medium text-red-700">Atur jumlah item atau hapus menu yang tidak jadi dipesan.</p>
                        </div>
                    </div>

                    <div id="cart-items-container" class="space-y-4">
                        <div class="rounded-3xl border border-dashed border-red-100 bg-white px-6 py-12 text-center text-red-400">
                            <i class="fas fa-circle-notch fa-spin text-3xl text-red-400"></i>
                            <p class="mt-4 text-sm font-medium text-red-600">Memuat keranjang...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-[28px] border border-slate-200/80 bg-white/95 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.05)] backdrop-blur sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-red-300">Ringkasan Belanja</p>
                    <div class="mt-4 rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-red-700">Total yang perlu kamu siapkan</p>
                                    <p class="mt-2 text-xs text-red-400">Belum termasuk ongkir jika memilih delivery.</p>
                                </div>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-red-100 bg-white text-red-500">
                                    <i class="fas fa-wallet text-lg"></i>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <span id="cart-total-display" class="block text-3xl font-semibold tracking-tight text-red-700">Rp 0</span>
                                <p class="mt-1 text-xs font-medium text-red-400">Total akan otomatis berubah saat jumlah item diperbarui.</p>
                            </div>
                    </div>

                    <div id="cart-action-buttons" class="mt-6 space-y-3">
                        <button id="btn-checkout"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-white"
                            disabled>
                            <i class="fas fa-credit-card"></i>
                            Checkout Sekarang
                        </button>
                        <button id="btn-clear-cart"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-3.5 text-base font-semibold text-red-700 transition hover:bg-red-50 hover:text-red-900">
                            <i class="fas fa-trash-alt"></i>
                            Kosongkan Keranjang
                        </button>
                    </div>
                </div>

                <div id="cart-checkout-section"
                    class="hidden rounded-[28px] border border-slate-200/80 bg-white/95 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.05)] backdrop-blur sm:p-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-red-300">Detail Checkout</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-red-900">Lengkapi data pengiriman</h2>
                        <p class="mt-2 text-sm leading-6 text-red-700">
                            Isi data seperlunya supaya pesanan bisa diproses dengan lancar.
                        </p>
                    </div>

                    <form id="checkoutForm" class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="checkout_jenis" class="mb-2 block text-sm font-semibold text-red-800">Jenis Belanja</label>
                                <select id="checkout_jenis" required
                                    class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100">
                                    <option value="Take Away">Take Away</option>
                                    <option value="Delivery">Delivery</option>
                                </select>
                            </div>
                            <div>
                                <label for="checkout_nama" class="mb-2 block text-sm font-semibold text-red-800">Nama Lengkap</label>
                                <input type="text" id="checkout_nama" required value="{{ Auth::check() ? Auth::user()->name : '' }}"
                                    class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition placeholder:text-red-300 focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100"
                                    placeholder="Nama penerima">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="checkout_no_hp" class="mb-2 block text-sm font-semibold text-red-800">No. HP / WhatsApp</label>
                                <input type="tel" id="checkout_no_hp" required value="{{ Auth::check() ? Auth::user()->no_hp : '' }}"
                                    class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition placeholder:text-red-300 focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                            <div>
                                <label for="checkout_payment_method" class="mb-2 block text-sm font-semibold text-red-800">Metode Pembayaran</label>
                                <select id="checkout_payment_method" required
                                    class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100">
                                    <option value="qris">QRIS</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                </select>
                                <p id="checkout-payment-hint" class="mt-2 text-xs leading-5 text-red-500">
                                    Pilih metode pembayaran yang paling nyaman untukmu.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                            <label for="checkout_outlet_search" class="mb-2 block text-sm font-semibold text-red-800">Cari Area Outlet</label>
                            <input type="text" id="checkout_outlet_search"
                                class="block w-full rounded-2xl border border-red-100 bg-white px-4 py-3 text-sm text-red-900 shadow-sm transition placeholder:text-red-300 focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                                placeholder="Contoh: Jakarta Barat, Kebon Jeruk, Bandung">
                            <p class="mt-2 text-xs leading-5 text-red-500">
                                Ketik kota, kecamatan, atau nama outlet supaya rekomendasinya lebih cepat muncul.
                            </p>
                        </div>

                        <div>
                            <label for="checkout_outlet_id" class="mb-2 block text-sm font-semibold text-red-800">Pilih Outlet</label>
                            <select id="checkout_outlet_id" required
                                class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100">
                                <option value="">Pilih outlet terdekat</option>
                            </select>
                            <p id="checkout-outlet-helper" class="mt-2 text-xs leading-5 text-red-500">
                                Pilih outlet yang paling dekat dengan area pengirimanmu.
                            </p>
                        </div>

                        <div id="checkout-outlet-preview"
                            class="hidden rounded-2xl border border-red-100 bg-red-50/40 px-4 py-4 text-sm text-red-800 shadow-inner"></div>

                        <div id="checkout-alamat-wrapper">
                            <label class="mb-2 block text-sm font-semibold text-red-800">Alamat Pengiriman</label>

                            <div id="address-options" class="hidden mb-3">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-700 transition hover:bg-red-50">
                                    <input type="checkbox" id="use_saved_address" class="mt-1 h-4 w-4 accent-red-700">
                                    <span>
                                        Gunakan alamat tersimpan:
                                        <strong id="saved-address-preview" class="mt-1 block text-red-900"></strong>
                                    </span>
                                </label>
                            </div>

                            <textarea id="checkout_alamat" rows="4" required
                                class="block w-full rounded-2xl border border-red-100 bg-red-50/40 px-4 py-3 text-sm text-red-900 shadow-sm transition placeholder:text-red-300 focus:border-red-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-100"
                                placeholder="Masukkan alamat lengkap pengiriman">{{ Auth::check() ? Auth::user()->alamat : '' }}</textarea>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                            <button type="submit"
                                class="flex-1 rounded-2xl bg-slate-900 px-4 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                Selesaikan Pesanan
                            </button>
                            <button type="button" id="btn-back-to-cart"
                                class="rounded-2xl border border-red-200 bg-white px-6 py-3.5 text-base font-semibold text-red-700 transition hover:bg-red-50 hover:text-red-900">
                                Kembali
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="userOrdersModal" class="fixed inset-0 z-[2005] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-4xl overflow-hidden rounded-[28px] bg-white text-left shadow-2xl">
            <div class="px-4 pb-4 pt-5 sm:px-6 sm:pb-6 sm:pt-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                            <i class="fas fa-receipt text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-red-900">Pesanan Saya</h3>
                            <p class="mt-1 text-sm text-red-700">Pantau status pembayaran dan progres pesananmu di satu tempat.</p>
                        </div>
                    </div>
                    <button type="button" id="closeUserOrdersModal"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-red-100 bg-white text-red-400 transition hover:bg-red-50 hover:text-red-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div id="user-orders-summary" class="mb-4 flex flex-wrap gap-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700">
                        <span class="font-semibold">Total</span>
                        <span class="font-bold text-red-900">0</span>
                    </div>
                </div>

                <div id="user-orders-list" class="space-y-3 max-h-[62vh] overflow-y-auto pr-1 sm:pr-2">
                    <div class="rounded-2xl border border-dashed border-red-100 bg-red-50/40 p-8 text-center text-sm text-red-700">
                        Riwayat pesanan akan muncul di sini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $checkoutCurrentUserData = Auth::check()
        ? [
            'id' => Auth::id(),
            'name' => Auth::user()->name,
            'no_hp' => Auth::user()->no_hp,
            'alamat' => Auth::user()->alamat,
            'role' => Auth::user()->role,
        ]
        : null;
@endphp

@push('scripts')
<script>
    const PRODUCTS_DATA = @json($productsData);
    const OUTLETS_DATA = @json($outletsData ?? []);
    const APP_BASE_URL = @json(rtrim(url('/'), '/'));
    const CHECKOUT_URL = @json(route('checkout'));
    const USER_ORDERS_PAGE_URL = @json(route('user.orders.page'));
    const CURRENT_USER_DATA = @json($checkoutCurrentUserData);
    let CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
@endpush
