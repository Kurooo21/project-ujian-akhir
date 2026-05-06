@extends('layouts.frontend')
@section('title', 'Riwayat Pesanan - Chi-Pok')

@push('styles')
<style>
    #main-header {
        display: none !important;
    }

    main {
        margin-top: 0 !important;
        background: transparent !important;
    }

    .orders-shell {
        background:
            radial-gradient(circle at top left, rgba(148, 163, 184, 0.12), transparent 28%),
            radial-gradient(circle at bottom right, rgba(226, 232, 240, 0.7), transparent 26%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 48%, #f8fafc 100%);
    }
</style>
@endpush

@php
    $previousUrl = url()->previous();
    $backUrl = $previousUrl && $previousUrl !== url()->current() ? $previousUrl : route('checkout');

    $userOrdersCurrentUserData = Auth::check()
        ? [
            'id' => Auth::id(),
            'name' => Auth::user()->name,
            'no_hp' => Auth::user()->no_hp,
            'alamat' => Auth::user()->alamat,
            'role' => Auth::user()->role,
        ]
        : null;
@endphp

@section('content')
<section class="orders-shell min-h-screen px-4 py-5 sm:px-6 lg:px-8" data-user-orders-page="true">
    <div class="mx-auto flex max-w-4xl items-center gap-3">
        <a href="{{ $backUrl }}"
            class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:border-red-300 hover:bg-red-50 hover:text-red-900">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Kembali</span>
        </a>
    </div>

    <div class="mx-auto max-w-4xl pb-16 pt-6">
        <div class="relative overflow-hidden rounded-[28px] bg-white text-left shadow-2xl ring-1 ring-slate-200">
            <div class="relative px-4 pb-4 pt-5 sm:px-6 sm:pb-6 sm:pt-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                            <i class="fas fa-receipt text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-red-900 sm:text-2xl">Pesanan Saya</h3>
                            <p class="mt-1 text-sm text-red-700">Lihat status pembayaran dan progres pesananmu dengan tampilan yang lebih sederhana.</p>
                        </div>
                    </div>
                    <a href="{{ $backUrl }}" id="btn-close-user-orders-page"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-red-100 bg-white text-red-400 transition hover:bg-red-50 hover:text-red-600">
                        <i class="fas fa-times text-base"></i>
                    </a>
                </div>

                <div id="user-orders-summary" class="mb-4 flex flex-wrap gap-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700">
                        <span class="font-semibold">Total</span>
                        <span class="font-bold text-red-900">0</span>
                    </div>
                </div>

                <div class="w-full">
                    <div id="user-orders-list" class="space-y-3 max-h-[70vh] overflow-y-auto pr-1 sm:pr-2">
                        <div class="rounded-2xl border border-dashed border-red-100 bg-red-50/40 p-8 text-center text-sm text-red-700">
                            Riwayat pesanan akan muncul di sini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const PRODUCTS_DATA = @json($productsData);
    const OUTLETS_DATA = @json($outletsData ?? []);
    const APP_BASE_URL = @json(rtrim(url('/'), '/'));
    const CHECKOUT_URL = @json(route('checkout'));
    const USER_ORDERS_PAGE_URL = @json(route('user.orders.page'));
    const CURRENT_USER_DATA = @json($userOrdersCurrentUserData);
    let CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
@endpush
