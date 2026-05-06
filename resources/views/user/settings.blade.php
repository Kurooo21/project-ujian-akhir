@extends('layouts.frontend')
@section('title', 'Pengaturan Profil - Chi-Pok')

{{-- Sembunyikan navbar dan hilangkan margin top bawaan layout --}}
@push('styles')
<style>
    /* Sembunyikan header/navbar di halaman ini */
    #main-header { display: none !important; }
    /* Hilangkan margin top karena navbar tidak ada */
    main { margin-top: 0 !important; }
    /* Custom scrollbar untuk area konten yang bisa di-scroll */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #D20000; border-radius: 10px; }
</style>
@endpush

@section('content')
{{-- Wrapper utama dengan background gambar menu dan overlay gelap --}}
<div class="relative min-h-screen flex items-center justify-center p-4 py-8 bg-menu-pattern bg-cover bg-center bg-fixed">
    {{-- Overlay gelap semi-transparan agar card lebih terbaca --}}
    <div class="absolute inset-0 bg-black/65 backdrop-blur-sm"></div>

    {{-- Tombol Back ke Beranda --}}
    <a href="{{ route('home') }}"
       class="fixed top-5 left-5 z-50 flex items-center gap-2 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white font-semibold px-4 py-2.5 rounded-full shadow-lg border border-white/30 transition-all duration-300 hover:-translate-x-1 text-sm">
        <i class="fas fa-arrow-left"></i>
        <span class="hidden sm:inline">Kembali</span>
    </a>

    {{-- Card Utama Pengaturan --}}
    <div class="relative z-10 w-full max-w-[940px] bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-500 mt-8">

        {{-- Panel Kiri - Branding dengan dekorasi lingkaran --}}
        <div class="hidden md:flex md:w-[40%] bg-gradient-to-br from-[#D20000] via-[#B30000] to-[#7A0000] flex-col items-center justify-center p-10 relative overflow-hidden">
            {{-- Dekorasi lingkaran blur di belakang --}}
            <div class="absolute -top-20 -left-20 w-64 h-64 bg-white/10 rounded-full blur-sm"></div>
            <div class="absolute -bottom-16 -right-16 w-52 h-52 bg-white/5 rounded-full blur-md"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-white/5 rounded-full"></div>
            {{-- Garis dekoratif diagonal --}}
            <div class="absolute top-0 right-0 w-1 h-full bg-gradient-to-b from-transparent via-white/20 to-transparent"></div>

            {{-- Icon dan teks branding --}}
            <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mb-6 relative z-10 shadow-inner">
                <i class="fas fa-user-cog text-5xl text-white"></i>
            </div>
            <h2 class="font-heading text-3xl text-white text-center tracking-widest relative z-10 mb-3">PENGATURAN</h2>
            <p class="text-white/70 text-sm text-center max-w-[220px] relative z-10 leading-relaxed">Kelola profil dan informasi akun Chi-Pok kamu di sini.</p>

            {{-- Dots dekoratif --}}
            <div class="mt-8 flex gap-3 relative z-10">
                <span class="w-6 h-2 rounded-full bg-white"></span>
                <span class="w-2 h-2 rounded-full bg-white/40"></span>
                <span class="w-2 h-2 rounded-full bg-white/40"></span>
            </div>

            {{-- Info user yang sedang login --}}
            <div class="absolute bottom-6 left-0 right-0 px-10 z-10">
                <div class="bg-white/10 rounded-2xl p-3 text-center border border-white/20">
                    <p class="text-white/60 text-xs">Masuk sebagai</p>
                    <p class="text-white font-bold text-sm truncate">{{ Auth::user()->name ?? Auth::user()->username }}</p>
                    @if(Auth::user()->role === 'admin')
                        <span class="text-xs bg-yellow-400 text-yellow-900 font-bold px-2 py-0.5 rounded-full">ADMIN</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panel Kanan - Form Pengaturan --}}
        <div class="flex-1 p-7 md:p-9 flex flex-col justify-start max-h-[90vh] overflow-y-auto custom-scrollbar">

            {{-- Header kecil untuk mobile (logo) --}}
            <div class="flex md:hidden justify-center mb-5">
                <div class="w-14 h-14 bg-gradient-to-br from-[#D20000] to-[#8B0000] rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                    <i class="fas fa-user-cog text-2xl text-white"></i>
                </div>
            </div>

            {{-- Tab Switcher khusus untuk admin --}}
            @if(Auth::check() && Auth::user()->role === 'admin')
            <div id="settings-tab-slider" class="flex p-1 bg-gray-100 rounded-xl mb-6 border border-gray-200">
                <button id="btn-tab-profile" type="button" class="flex-1 py-2 rounded-lg bg-white shadow-sm text-sm font-bold text-red-600 transition-all">Profil Saya</button>
                <button id="btn-tab-banner" type="button" class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-gray-700 transition-all">Edit Banner</button>
                <button id="btn-tab-outlet" type="button" class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-gray-700 transition-all">Outlet &amp; Demo Payment</button>
            </div>
            @endif

            {{-- === CONTAINER: PROFIL SAYA === --}}
            <div id="container-profile">
                <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">PROFIL SAYA</h3>
                <p class="text-gray-400 text-sm mb-6">Kelola informasi akun kamu</p>

                {{-- Notifikasi error dari validasi form --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                        <span class="font-medium">Opps!</span>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Notifikasi sukses setelah simpan --}}
                @if (session('success'))
                    <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Form Edit Profil --}}
                <form action="{{ route('api.user.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Input Nama Lengkap --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                <i class="fas fa-id-card text-sm"></i>
                            </span>
                            <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required placeholder="Nama lengkapmu"
                                class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                        </div>
                    </div>

                    {{-- Input Username --}}
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input type="text" id="username" name="username" value="{{ old('username', Auth::user()->username) }}" required
                                class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                        </div>
                    </div>

                    {{-- Dua kolom: No HP & Alamat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="no_hp" class="block text-sm font-semibold text-gray-600 mb-1">No. HP</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-phone text-sm"></i>
                                </span>
                                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp', Auth::user()->no_hp) }}" placeholder="08xxxxxxxxxx"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                        </div>
                        <div>
                            <label for="alamat" class="block text-sm font-semibold text-gray-600 mb-1">Alamat</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-3 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                    <i class="fas fa-map-marker-alt text-sm"></i>
                                </span>
                                <textarea id="alamat" name="alamat" rows="1" placeholder="Alamat lengkap"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300 resize-none">{{ old('alamat', Auth::user()->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Input Password (opsional) --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-600 mb-1">Password Baru <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin ubah"
                                class="w-full pl-11 pr-12 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                        </div>
                    </div>

                    {{-- Tombol Simpan --}}
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-[#D20000] to-[#FF2E00] text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2 text-sm tracking-wide mt-2">
                        <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>

            {{-- === CONTAINER ADMIN: EDIT BANNER === --}}
            @if(Auth::check() && Auth::user()->role === 'admin')
            <div id="container-banner" class="hidden">
                <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">EDIT BANNER</h3>
                <p class="text-gray-400 text-sm mb-6">Kelola gambar promosi halaman utama</p>

                <div class="max-h-[50vh] overflow-y-auto custom-scrollbar pr-2">
                    {{-- Form Tambah Banner --}}
                    <form id="form-add-banner" class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Gambar Banner</label>
                            <input type="file" id="banner-image" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer transition" required>
                            <p class="text-[11px] text-gray-500 mt-2 p-2 bg-gray-50 rounded-lg border border-gray-100 leading-tight">
                                💡 <strong>Rekomendasi Responsif:</strong><br/>
                                Rasio <strong>2:1</strong> / <strong>16:9</strong> (Contoh: 1920x1080px). Pusatkan desain inti (Center-safe).
                            </p>
                        </div>
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input type="text" id="banner-desc" name="description" placeholder="Contoh: Promo Lebaran" class="w-full px-3 py-2 border rounded-lg focus:ring-red-500 focus:border-red-500 outline-none transition text-sm">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-gray-800 to-gray-900 text-white font-bold rounded-lg shadow hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-upload"></i> Unggah Banner
                        </button>
                    </form>

                    {{-- Daftar Banner Aktif --}}
                    <div>
                        <h5 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-widest border-b pb-2">Daftar Banner Aktif</h5>
                        <div id="banner-list-container" class="grid grid-cols-1 gap-3">
                            <div class="col-span-full text-center text-sm text-gray-500 py-4">Memuat banner...</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === CONTAINER ADMIN: OUTLET & DEMO PAYMENT === --}}
            <div id="container-outlet" class="hidden">
                <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">PENGATURAN OUTLET &amp; DEMO PAYMENT</h3>
                <p class="text-gray-400 text-sm mb-6">Kelola alamat outlet, WhatsApp admin, QRIS demo, dan rekening demo untuk checkout pelanggan</p>

                <form id="form-edit-outlet" class="space-y-4">
                    {{-- Alamat Outlet --}}
                    <div>
                        <label for="input_outlet_address" class="block text-sm font-semibold text-gray-600 mb-1">Alamat Outlet</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-3 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                <i class="fas fa-map-marker-alt text-sm"></i>
                            </span>
                            <textarea id="input_outlet_address" name="outlet_address" rows="3" placeholder="Masukkan alamat outlet..."
                                class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300 resize-none">{{ $settings['outlet_address'] ?? '' }}</textarea>
                        </div>
                    </div>

                    {{-- Nomor WhatsApp Admin --}}
                    <div>
                        <label for="input_admin_whatsapp" class="block text-sm font-semibold text-gray-600 mb-1">Nomor WhatsApp Admin</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                                <i class="fab fa-whatsapp text-sm"></i>
                            </span>
                            <input type="tel" id="input_admin_whatsapp" name="admin_whatsapp_number" placeholder="628xxxxxxxxxx"
                                value="{{ $settings['admin_whatsapp_number'] ?? '' }}"
                                class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Gunakan format angka aktif WhatsApp, misalnya 6281336441994.</p>
                    </div>

                    {{-- Pengaturan Demo QRIS --}}
                    <div class="pt-2 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3">Pengaturan Demo QRIS</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="input_payment_qris_label" class="block text-sm font-semibold text-gray-600 mb-1">Label QRIS Demo</label>
                                <input type="text" id="input_payment_qris_label" name="payment_qris_label" placeholder="Contoh: Demo QRIS Chi-Pok"
                                    value="{{ $settings['payment_qris_label'] ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label for="input_payment_qris_image_url" class="block text-sm font-semibold text-gray-600 mb-1">Link / Path Gambar QRIS Demo</label>
                                <input type="text" id="input_payment_qris_image_url" name="payment_qris_image_url" placeholder="/asset/qris-chipok.png atau https://..."
                                    value="{{ $settings['payment_qris_image_url'] ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label for="input_payment_qris_note" class="block text-sm font-semibold text-gray-600 mb-1">Catatan QRIS Demo</label>
                                <textarea id="input_payment_qris_note" name="payment_qris_note" rows="2" placeholder="Contoh: Ini hanya QRIS demo untuk presentasi."
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300 resize-none">{{ $settings['payment_qris_note'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Pengaturan Demo Transfer Bank --}}
                    <div class="pt-2 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3">Pengaturan Demo Transfer Bank</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="input_payment_bank_name" class="block text-sm font-semibold text-gray-600 mb-1">Nama Bank Demo</label>
                                <input type="text" id="input_payment_bank_name" name="payment_bank_name" placeholder="Contoh: BCA Demo"
                                    value="{{ $settings['payment_bank_name'] ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label for="input_payment_bank_account_number" class="block text-sm font-semibold text-gray-600 mb-1">Nomor Rekening</label>
                                <input type="text" id="input_payment_bank_account_number" name="payment_bank_account_number" placeholder="Contoh: 1234567890"
                                    value="{{ $settings['payment_bank_account_number'] ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label for="input_payment_bank_account_name" class="block text-sm font-semibold text-gray-600 mb-1">Nama Pemilik Rekening</label>
                                <input type="text" id="input_payment_bank_account_name" name="payment_bank_account_name" placeholder="Contoh: Chi Pok Indonesia"
                                    value="{{ $settings['payment_bank_account_name'] ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                            </div>
                            <div>
                                <label for="input_payment_bank_note" class="block text-sm font-semibold text-gray-600 mb-1">Catatan Transfer Bank Demo</label>
                                <textarea id="input_payment_bank_note" name="payment_bank_note" rows="2" placeholder="Contoh: Ini hanya rekening demo untuk simulasi."
                                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300 resize-none">{{ $settings['payment_bank_note'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Simpan Pengaturan Outlet --}}
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-[#D20000] to-[#FF2E00] text-white font-bold rounded-xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2 text-sm tracking-wide mt-4">
                        <i class="fas fa-save"></i> SIMPAN PENGATURAN
                    </button>
                </form>
            </div>
            @endif

        </div>{{-- End panel kanan --}}
    </div>{{-- End card utama --}}
</div>
@endsection

@push('scripts')
<script>
    // Variabel global yang dibutuhkan oleh app.js
    const APP_BASE_URL = @json(rtrim(url('/'), '/'));
    let CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
@endpush
