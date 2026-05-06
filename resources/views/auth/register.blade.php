@extends('layouts.frontend')
@section('title', 'Daftar Akun - Chi-Pok')

@push('styles')
<style>
    /* Sembunyikan navbar dari layout utama */
    #main-header { display: none !important; }
    /* Hilangkan margin top dari main tag di layout utama */
    main { margin-top: 0 !important; }
</style>
@endpush

@section('content')
<!-- Container utama dengan background gambar -->
<div class="relative min-h-screen flex items-center justify-center p-4 bg-menu-pattern bg-cover bg-center bg-fixed">

    
    <!-- Card Register -->
    <div class="relative z-10 w-full max-w-[900px] bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-500">
        
        <!-- Left — Branding Panel -->
        <div class="hidden md:flex md:w-[45%] bg-gradient-to-br from-[#D20000] via-[#B30000] to-[#8B0000] flex-col items-center justify-center p-10 relative overflow-hidden">
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
            
            <div class="flex md:hidden justify-center mb-5">
                <div class="w-14 h-14 bg-gradient-to-br from-[#D20000] to-[#8B0000] rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                    <img src="{{ asset('asset/logo putih.png') }}" alt="Chi-Pok" class="h-9 brightness-0 invert">
                </div>
            </div>

            <h3 class="font-heading text-3xl text-gray-900 tracking-wide mb-1">DAFTAR</h3>
            <p class="text-gray-400 text-sm mb-6">Buat akun baru di Chi-Pok</p>

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

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="nama@email.com"
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                        <input type="text" id="username" name="username" required value="{{ old('username') }}" placeholder="Pilih username"
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                    </div>
                </div>

                <!-- No. WhatsApp Aktif -->
                <div>
                    <label for="no_hp" class="block text-sm font-semibold text-gray-600 mb-1">No. WhatsApp Aktif</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                            <i class="fab fa-whatsapp text-sm"></i>
                        </span>
                        <input type="tel" id="no_hp" name="no_hp" required value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" id="password" name="password" required placeholder="Buat password"
                            class="w-full pl-11 pr-12 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50/50 text-sm focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 outline-none transition-all duration-300">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-[#D20000] to-[#FF2E00] hover:from-[#B30000] hover:to-[#D20000] text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 mt-4">
                    <i class="fas fa-user-plus"></i> DAFTAR SEKARANG
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-5">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-red-600 font-bold hover:text-red-700 hover:underline transition-colors">Masuk disini</a>
            </p>
        </div>
    </div>
</div>
@endsection
