@extends('admin.layouts.app')
@section('page_title', 'Manajemen Outlet')

@section('content')
@php
    $totalOutlet = $outlets->count();
    $activeOutlet = $outlets->where('is_active', true)->count();
@endphp

@if(session('success'))
    <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <div class="font-semibold">Data outlet belum lengkap.</div>
        <ul class="mt-2 list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Outlet</h2>
                <span class="text-xs text-slate-400">Kelola outlet aktif yang nanti bisa dipilih user saat checkout</span>
            </div>
            <button type="button" onclick="openOutletModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[13px] font-semibold transition-colors shadow-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Outlet
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                        <th class="py-3 px-6">Outlet</th>
                        <th class="py-3 px-6">Area</th>
                        <th class="py-3 px-6">Alamat</th>
                        <th class="py-3 px-6">Kontak</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                    @forelse($outlets as $outlet)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="font-semibold text-slate-900">{{ $outlet->name }}</div>
                            <div class="text-[11px] text-slate-400">Urutan: {{ $outlet->sort_order }}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="font-medium text-slate-700">{{ $outlet->district ?: '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $outlet->city }}{{ $outlet->province ? ', ' . $outlet->province : '' }}</div>
                        </td>
                        <td class="py-3.5 px-6 max-w-[260px]">
                            <div class="line-clamp-2" title="{{ $outlet->address }}">{{ $outlet->address }}</div>
                            @if($outlet->maps_url)
                                <a href="{{ $outlet->maps_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-700 mt-1">
                                    Buka Maps
                                </a>
                            @endif
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="text-slate-700">{{ $outlet->phone ?: '-' }}</span>
                        </td>
                        <td class="py-3.5 px-6">
                            @if($outlet->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition-colors"
                                    data-outlet="{{ e(json_encode([
                                        'id' => $outlet->id,
                                        'name' => $outlet->name,
                                        'province' => $outlet->province,
                                        'city' => $outlet->city,
                                        'district' => $outlet->district,
                                        'address' => $outlet->address,
                                        'phone' => $outlet->phone,
                                        'maps_url' => $outlet->maps_url,
                                        'sort_order' => $outlet->sort_order,
                                        'is_active' => $outlet->is_active,
                                    ])) }}"
                                    onclick="openEditOutletModal(this.dataset.outlet)">
                                    Edit
                                </button>
                                <form action="{{ route('admin.outlet.destroy', $outlet) }}" method="POST" onsubmit="return confirm('Hapus outlet ini?')">
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
                        <td colspan="6" class="text-center py-10 text-slate-400">Belum ada outlet yang ditambahkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Ringkasan Outlet</h3>
            <div class="space-y-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Total Outlet</div>
                    <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalOutlet }}</div>
                </div>
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4">
                    <div class="text-[11px] uppercase tracking-widest text-green-500 font-semibold">Outlet Aktif</div>
                    <div class="mt-2 text-3xl font-extrabold text-green-700">{{ $activeOutlet }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Saran Pengisian</h3>
            <ul class="space-y-3 text-sm text-slate-600 leading-relaxed">
                <li>Isi kota dan kecamatan dengan jelas supaya checkout bisa memberi rekomendasi outlet yang relevan ke user.</li>
                <li>Gunakan urutan kecil pada outlet yang paling sering dipilih agar tampil lebih atas.</li>
                <li>Nonaktifkan outlet yang sedang tutup tanpa harus menghapus datanya.</li>
            </ul>
        </div>
    </div>
</div>

<div id="outletModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeOutletModal()"></div>

        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            <div class="bg-white px-6 pb-6 pt-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 id="outletModalTitle" class="text-lg font-bold text-slate-900">Tambah Outlet</h3>
                    <button type="button" onclick="closeOutletModal()" class="w-9 h-9 rounded-full border border-slate-200 text-slate-400 hover:text-red-500 hover:bg-red-50 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form
                    id="outletForm"
                    method="POST"
                    action="{{ route('admin.outlet.store') }}"
                    data-store-action="{{ route('admin.outlet.store') }}"
                    data-update-action-template="{{ route('admin.outlet.update', ['outlet' => '__OUTLET__']) }}"
                    class="space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_method" id="outletMethodInput" value="POST">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Nama Outlet</label>
                            <input type="text" name="name" id="outlet_name" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Provinsi</label>
                            <input type="text" name="province" id="outlet_province" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kota / Kabupaten</label>
                            <input type="text" name="city" id="outlet_city" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kecamatan / Area</label>
                            <input type="text" name="district" id="outlet_district" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">No. Kontak Outlet</label>
                            <input type="text" name="phone" id="outlet_phone" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                            <textarea name="address" id="outlet_address" rows="3" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5"></textarea>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Link Google Maps</label>
                            <input type="url" name="maps_url" id="outlet_maps_url" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="outlet_sort_order" min="0" value="0" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="is_active" id="outlet_is_active" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" checked>
                        Outlet aktif dan bisa dipilih user saat checkout
                    </label>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50" onclick="closeOutletModal()">Batal</button>
                        <button type="submit" id="outletSubmitButton" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Outlet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const outletModal = document.getElementById('outletModal');
    const outletForm = document.getElementById('outletForm');
    const outletMethodInput = document.getElementById('outletMethodInput');
    const outletModalTitle = document.getElementById('outletModalTitle');
    const outletSubmitButton = document.getElementById('outletSubmitButton');
    const outletStoreAction = outletForm.dataset.storeAction;
    const outletUpdateActionTemplate = outletForm.dataset.updateActionTemplate;

    function resetOutletFormFields() {
        document.getElementById('outlet_name').value = '';
        document.getElementById('outlet_province').value = '';
        document.getElementById('outlet_city').value = '';
        document.getElementById('outlet_district').value = '';
        document.getElementById('outlet_phone').value = '';
        document.getElementById('outlet_address').value = '';
        document.getElementById('outlet_maps_url').value = '';
        document.getElementById('outlet_sort_order').value = 0;
        document.getElementById('outlet_is_active').checked = true;
    }

    function setOutletFormMode(mode, outlet = null) {
        const isEditMode = mode === 'edit' && outlet;

        outletModalTitle.textContent = isEditMode ? 'Edit Outlet' : 'Tambah Outlet';
        outletSubmitButton.textContent = isEditMode ? 'Perbarui Outlet' : 'Simpan Outlet';
        outletForm.action = isEditMode
            ? outletUpdateActionTemplate.replace('__OUTLET__', outlet.id)
            : outletStoreAction;
        outletMethodInput.value = isEditMode ? 'PUT' : 'POST';

        if (!isEditMode) {
            resetOutletFormFields();
            return;
        }

        document.getElementById('outlet_name').value = outlet.name || '';
        document.getElementById('outlet_province').value = outlet.province || '';
        document.getElementById('outlet_city').value = outlet.city || '';
        document.getElementById('outlet_district').value = outlet.district || '';
        document.getElementById('outlet_phone').value = outlet.phone || '';
        document.getElementById('outlet_address').value = outlet.address || '';
        document.getElementById('outlet_maps_url').value = outlet.maps_url || '';
        document.getElementById('outlet_sort_order').value = outlet.sort_order ?? 0;
        document.getElementById('outlet_is_active').checked = !!outlet.is_active;
    }

    function openOutletModal() {
        setOutletFormMode('create');
        outletModal.classList.remove('hidden');
    }

    function closeOutletModal() {
        outletModal.classList.add('hidden');
        setOutletFormMode('create');
    }

    function openEditOutletModal(outletPayload) {
        const outlet = typeof outletPayload === 'string'
            ? JSON.parse(outletPayload)
            : outletPayload;

        setOutletFormMode('edit', outlet);
        outletModal.classList.remove('hidden');
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !outletModal.classList.contains('hidden')) {
            closeOutletModal();
        }
    });
</script>
@endsection
