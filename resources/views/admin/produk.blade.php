@extends('admin.layouts.app')
@section('page_title', 'Data Produk')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 lg:px-6 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Katalog Menu & Produk</h2>
            <span class="text-xs text-slate-400">Kelola seluruh produk Chi-Pok</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[13px] font-semibold transition-colors shadow-sm" onclick="openAddMenuModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Produk
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">No</th>
                    <th class="py-3 px-6">Produk</th>
                    <th class="py-3 px-6">Kategori</th>
                    <th class="py-3 px-6">Harga</th>
                    <th class="py-3 px-6">Deskripsi</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($products as $index => $produk)
                <tr class="hover:bg-blue-50/50 transition-colors">
                    <td class="py-3.5 px-6">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                @if(Str::startsWith($produk->image, 'http'))
                                    <img src="{{ $produk->image }}" alt="{{ $produk->name }}" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ asset($produk->image) }}" alt="{{ $produk->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="font-semibold text-slate-900">{{ $produk->name }}</div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize bg-slate-100 text-slate-600 border border-slate-200">
                            {{ $produk->category ?? 'makanan' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 font-semibold text-slate-900">Rp {{ number_format($produk->price, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-6 truncate max-w-[200px]" title="{{ $produk->description }}">{{ Str::limit($produk->description, 50) }}</td>
                    <td class="py-3.5 px-6 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('products.destroy', $produk->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-md transition-colors" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-slate-400">Belum ada data produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================
     MODAL TAMBAH MENU (Admin Panel)
     ============================================================ -->
<div id="addMenuModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <!-- Overlay gelap -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeAddMenuModal()"></div>
        
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-6 pb-6 pt-6">
                <h3 class="text-lg font-bold text-slate-900 mb-5">Tambah Menu Baru</h3>
                <form id="addMenuForm" class="space-y-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Nama Menu</label>
                        <input type="text" id="new_menu_name" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Harga</label>
                        <div class="flex rounded-md shadow-sm border border-slate-200 overflow-hidden">
                            <span class="inline-flex items-center px-3 bg-slate-50 text-slate-500 text-sm font-semibold border-r border-slate-200">Rp</span>
                            <input type="number" id="new_menu_price" required placeholder="25000" inputmode="numeric" class="block w-full border-0 focus:ring-blue-500 focus:border-blue-500 text-sm p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <textarea id="new_menu_desc" rows="3" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5"></textarea>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Gambar Menu</label>
                        <input type="file" id="new_menu_img" accept="image/*" class="block w-full text-sm text-slate-500 border border-slate-200 rounded-md p-1.5 file:mr-4 file:py-1.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                        <p class="mt-1 text-xs text-slate-400">PNG, JPG, JPEG (maks. 2MB)</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kategori</label>
                        <select id="new_menu_category" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5 bg-white">
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50" onclick="closeAddMenuModal()">Batal</button>
                        <button type="submit" id="btnSubmitMenu" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('addMenuModal');
    
    function openAddMenuModal() {
        modal.classList.remove('hidden');
    }

    function closeAddMenuModal() {
        modal.classList.add('hidden');
        document.getElementById('addMenuForm').reset();
    }

    document.getElementById('addMenuForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btnSubmitMenu');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Menyimpan...';

        const formData = new FormData();
        formData.append('name', document.getElementById('new_menu_name').value);
        formData.append('price', document.getElementById('new_menu_price').value);
        formData.append('description', document.getElementById('new_menu_desc').value);
        formData.append('category', document.getElementById('new_menu_category').value);
        
        const imageFile = document.getElementById('new_menu_img').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('{{ route("products.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            
            if(result.success) {
                alert('Menu berhasil ditambahkan!');
                window.location.reload();
            } else {
                alert('Gagal menyimpa menu: ' + (result.message || 'Error Server'));
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Simpan Menu';
            }
        } catch(err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Simpan Menu';
        }
    });
</script>
@endsection
