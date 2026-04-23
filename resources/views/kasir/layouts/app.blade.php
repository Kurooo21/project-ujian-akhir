<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir Panel - Chi-Pok</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        .swal2-container { z-index: 99999 !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen antialiased flex">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] hidden transition-opacity lg:hidden" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <aside class="fixed inset-y-0 left-0 bg-slate-900 text-white w-[260px] flex flex-col z-[1000] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out" id="sidebar">
        <div>
            <img src="{{ asset('asset/logo putih.png') }}" alt="Logo" class="w-15 h-15">
            <div>
                <span class="text-[15px] font-bold text-white uppercase tracking-widest ml-16">Kasir Panel</span>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto sidebar-scroll">
            <div class="text-[13px] font-semibold text-white uppercase tracking-widest px-3 pt-4 pb-2">Menu Kasir</div>

            @php
                $currentRoute = Route::currentRouteName();
            @endphp

            <a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all mb-1 {{ $currentRoute == 'kasir.dashboard' ? 'bg-white text-black shadow-[0_2px_8px_rgba(37,99,235,0.35)]' : 'text-slate-300 hover:bg-white hover:text-black' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 flex-shrink-0">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('kasir.pesanan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all mb-1 {{ $currentRoute == 'kasir.pesanan' ? 'bg-white text-black shadow-[0_2px_8px_rgba(37,99,235,0.35)]' : 'text-slate-300 hover:bg-white hover:text-black' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 flex-shrink-0">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                Pesanan Outlet
            </a>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <div class="flex items-center gap-3 px-3 py-2 rounded-md">
                <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center font-bold text-sm">
                    <img src="{{ asset('asset/logo merah.png') }}" alt="Logo" class="w-10 h-13">
                </div>
                <div>
                    <h4 class="text-[13px] font-semibold">{{ Auth::user()->name ?? 'Kasir' }}</h4>
                    <span class="text-[11px] text-slate-400">{{ Auth::user()->outlet?->name ?? 'Outlet belum diatur' }}</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-[260px]">
        <header class="sticky top-0 h-16 bg-white/85 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 lg:px-7 z-[500]">
            <div class="flex items-center gap-4">
                <button class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-md transition" onclick="openSidebar()" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard Kasir')</h1>
                    <p class="text-[13px] text-slate-400 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-md text-[13px] font-medium transition" onclick="handleLogout()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </div>
        </header>

        <div class="p-4 lg:p-7">
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    <div class="font-semibold mb-1">Ada data yang perlu dicek lagi:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        async function handleLogout() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                await fetch('{{ route("logout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                window.location.href = '{{ url("/") }}';
            } catch (err) {
                window.location.href = '{{ url("/") }}';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
