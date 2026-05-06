{{--
    ============================================================
    HALAMAN MANAJEMEN KASIR
    File: resources/views/admin/kasir.blade.php
    ============================================================
    Halaman ini digunakan Admin untuk mengelola akun kasir:
    - Melihat daftar semua kasir beserta outlet yang ditugaskan
    - Menambah akun kasir baru
    - Mengedit data kasir (nama, username, outlet, password, dll)
    - Menghapus akun kasir

    Aturan bisnis penting:
    - Satu outlet hanya boleh dipegang satu kasir
    - Kasir yang belum ditautkan ke outlet tidak bisa melihat pesanan

    Data dari AdminKasirController:
    - $kasirs  : Koleksi semua akun dengan role 'kasir'
    - $outlets : Semua outlet aktif (untuk pilihan dropdown di form)
    ============================================================
--}}
@extends('admin.layouts.app')
@section('page_title', 'Manajemen Kasir')

@section('content')

{{-- Hitung statistik kasir di sisi server (PHP) --}}
{{-- $totalKasir         = jumlah semua akun kasir --}}
{{-- $kasirDenganOutlet  = kasir yang sudah ditautkan ke outlet --}}
@php
    $totalKasir = $kasirs->count();
    $kasirDenganOutlet = $kasirs->whereNotNull('outlet_id')->count();
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Akun Kasir</h2>
                <span class="text-xs text-slate-400">Setiap kasir ditautkan ke satu outlet supaya tugas operasionalnya tetap rapi.</span>
            </div>
            <button type="button" onclick="openKasirModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[13px] font-semibold transition-colors shadow-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Kasir
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                        <th class="py-3 px-6">Kasir</th>
                        <th class="py-3 px-6">Username</th>
                        <th class="py-3 px-6">Outlet</th>
                        <th class="py-3 px-6">Kontak</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                    @forelse($kasirs as $kasir)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="font-semibold text-slate-900">{{ $kasir->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $kasir->email ?: 'Tanpa email' }}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                {{ $kasir->username }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="font-semibold text-slate-900">{{ $kasir->outlet?->name ?: 'Belum ditautkan' }}</div>
                            <div class="text-[11px] text-slate-400">
                                @if($kasir->outlet)
                                    {{ $kasir->outlet->district ?: '-' }}, {{ $kasir->outlet->city }}
                                @else
                                    Admin perlu memilih outlet
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-6">
                            <div>{{ $kasir->no_hp ?: '-' }}</div>
                            <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $kasir->alamat }}">{{ $kasir->alamat ?: 'Alamat belum diisi' }}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition-colors"
                                    data-kasir="{{ e(json_encode([
                                        'id' => $kasir->id,
                                        'name' => $kasir->name,
                                        'email' => $kasir->email,
                                        'username' => $kasir->username,
                                        'outlet_id' => $kasir->outlet_id,
                                        'no_hp' => $kasir->no_hp,
                                        'alamat' => $kasir->alamat,
                                    ])) }}"
                                    onclick="openEditKasirModal(this.dataset.kasir)">
                                    Edit
                                </button>
                                <form action="{{ route('admin.kasir.destroy', $kasir) }}" method="POST" onsubmit="return confirm('Hapus akun kasir ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-slate-400">Belum ada akun kasir.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- ============================================================
     PANEL RINGKASAN & CATATAN (Kolom Kanan)
     ============================================================ --}}
<div class="space-y-5">
    {{-- Widget: Statistik angka kasir --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-4">Ringkasan Kasir</h3>
        <div class="space-y-3">
            {{-- Total seluruh akun kasir --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Total Kasir</div>
                <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalKasir }}</div>
            </div>
            {{-- Kasir yang sudah punya outlet (bisa mulai bekerja) --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                <div class="text-[11px] uppercase tracking-widest text-blue-500 font-semibold">Kasir Tertaut Outlet</div>
                <div class="mt-2 text-3xl font-extrabold text-blue-700">{{ $kasirDenganOutlet }}</div>
            </div>
        </div>
    </div>

    {{-- Widget: Catatan aturan bisnis untuk admin --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-4">Catatan</h3>
        <ul class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <li>Satu outlet hanya boleh dipegang satu akun kasir agar alur operasional tidak bentrok.</li>
            <li>Username dipakai saat login. Password minimal 4 karakter untuk kebutuhan demo.</li>
            <li>Kalau outlet diganti, kasir otomatis akan melihat pesanan outlet barunya.</li>
        </ul>
    </div>
</div>
</div>

<div id="kasirModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeKasirModal()"></div>

        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            <div class="bg-white px-6 pb-6 pt-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 id="kasirModalTitle" class="text-lg font-bold text-slate-900">Tambah Kasir</h3>
                    <button type="button" onclick="closeKasirModal()" class="w-9 h-9 rounded-full border border-slate-200 text-slate-400 hover:text-red-500 hover:bg-red-50 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form
                    id="kasirForm"
                    method="POST"
                    action="{{ route('admin.kasir.store') }}"
                    data-store-action="{{ route('admin.kasir.store') }}"
                    data-update-action-template="{{ route('admin.kasir.update', ['kasir' => '__KASIR__']) }}"
                    class="space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_method" id="kasirMethodInput" value="POST">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Nama Kasir</label>
                            <input type="text" name="name" id="kasir_name" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Username</label>
                            <input type="text" name="username" id="kasir_username" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="kasir_email" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Outlet</label>
                            <select name="outlet_id" id="kasir_outlet_id" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5 bg-white">
                                <option value="">Pilih outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }} - {{ $outlet->district ?: '-' }}, {{ $outlet->city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">No. HP</label>
                            <input type="text" name="no_hp" id="kasir_no_hp" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Password</label>
                            <input type="password" name="password" id="kasir_password" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                            <p id="kasirPasswordHint" class="mt-1 text-[11px] text-slate-400">Minimal 4 karakter.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Alamat</label>
                            <textarea name="alamat" id="kasir_alamat" rows="3" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50" onclick="closeKasirModal()">Batal</button>
                        <button type="submit" id="kasirSubmitButton" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Kasir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const kasirModal = document.getElementById('kasirModal');
    const kasirForm = document.getElementById('kasirForm');
    const kasirMethodInput = document.getElementById('kasirMethodInput');
    const kasirModalTitle = document.getElementById('kasirModalTitle');
    const kasirSubmitButton = document.getElementById('kasirSubmitButton');
    const kasirPasswordHint = document.getElementById('kasirPasswordHint');
    const kasirStoreAction = kasirForm.dataset.storeAction;
    const kasirUpdateActionTemplate = kasirForm.dataset.updateActionTemplate;

    /**
     * resetKasirFormFields() — Kosongkan semua field di form kasir
     * Dipanggil sebelum modal dibuka dalam mode "Tambah" agar form bersih
     */
    function resetKasirFormFields() {
        document.getElementById('kasir_name').value = '';
        document.getElementById('kasir_username').value = '';
        document.getElementById('kasir_email').value = '';
        document.getElementById('kasir_outlet_id').value = '';
        document.getElementById('kasir_no_hp').value = '';
        document.getElementById('kasir_password').value = '';
        document.getElementById('kasir_alamat').value = '';
    }

    /**
     * setKasirFormMode(mode, kasir) — Atur form modal ke mode Tambah atau Edit
     *
     * @param {string} mode  - 'create' untuk tambah baru, 'edit' untuk update
     * @param {object} kasir - Data kasir (hanya diisi jika mode = 'edit')
     *
     * Cara kerjanya:
     * - Ubah judul modal, teks tombol, dan hint password sesuai mode
     * - Ubah action form dan method (POST untuk create, PUT untuk update)
     * - Jika edit: isi field form dengan data kasir yang ada
     * - Jika create: kosongkan semua field
     */
    function setKasirFormMode(mode, kasir = null) {
        const isEditMode = mode === 'edit' && kasir;

        // Atur teks tampilan sesuai mode
        kasirModalTitle.textContent = isEditMode ? 'Edit Kasir' : 'Tambah Kasir';
        kasirSubmitButton.textContent = isEditMode ? 'Perbarui Kasir' : 'Simpan Kasir';
        kasirPasswordHint.textContent = isEditMode
            ? 'Kosongkan password kalau tidak ingin mengubahnya.'
            : 'Minimal 4 karakter.';

        // Atur action & method form
        // Untuk update: ganti __KASIR__ di template URL dengan ID kasir yang diedit
        kasirForm.action = isEditMode
            ? kasirUpdateActionTemplate.replace('__KASIR__', kasir.id)
            : kasirStoreAction;
        kasirMethodInput.value = isEditMode ? 'PUT' : 'POST';

        // Jika mode create: kosongkan form dan selesai
        if (!isEditMode) {
            resetKasirFormFields();
            return;
        }

        // Jika mode edit: isi field form dengan data kasir yang ada
        document.getElementById('kasir_name').value = kasir.name || '';
        document.getElementById('kasir_username').value = kasir.username || '';
        document.getElementById('kasir_email').value = kasir.email || '';
        document.getElementById('kasir_outlet_id').value = kasir.outlet_id || '';
        document.getElementById('kasir_no_hp').value = kasir.no_hp || '';
        document.getElementById('kasir_password').value = ''; // Password selalu dikosongkan saat edit
        document.getElementById('kasir_alamat').value = kasir.alamat || '';
    }

    /** openKasirModal() — Buka modal dalam mode Tambah Kasir Baru */
    function openKasirModal() {
        setKasirFormMode('create'); // Set form ke mode tambah
        kasirModal.classList.remove('hidden'); // Tampilkan modal
    }

    /** closeKasirModal() — Tutup modal dan reset form ke mode tambah */
    function closeKasirModal() {
        kasirModal.classList.add('hidden'); // Sembunyikan modal
        setKasirFormMode('create');          // Reset form agar bersih
    }

    /**
     * openEditKasirModal(kasirPayload) — Buka modal dalam mode Edit Kasir
     *
     * @param {string|object} kasirPayload - Data kasir sebagai string JSON atau objek JS
     *
     * Kenapa bisa string atau objek?
     * → Saat dipanggil dari atribut HTML (onclick), data dikirim sebagai
     *   string JSON via data-kasir attribute. Fungsi ini memastikan
     *   data selalu dikonversi ke objek JS sebelum digunakan.
     */
    function openEditKasirModal(kasirPayload) {
        // Jika payload masih berupa string JSON, parse dulu ke objek
        const kasir = typeof kasirPayload === 'string'
            ? JSON.parse(kasirPayload)
            : kasirPayload;

        setKasirFormMode('edit', kasir); // Set form ke mode edit dengan data kasir
        kasirModal.classList.remove('hidden'); // Tampilkan modal
    }

    /**
     * Tutup modal saat user menekan tombol Escape di keyboard
     * Ini standar UX agar modal tidak "menjebak" user
     */
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !kasirModal.classList.contains('hidden')) {
            closeKasirModal();
        }
    });
</script>
@endsection
