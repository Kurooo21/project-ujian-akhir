@extends('admin.layouts.app')
@section('page_title', 'Bahan Baku')

@section('content')
<div class="bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-2xl p-6 shadow-sm mb-6">
    <div class="text-[11px] font-semibold uppercase tracking-widest text-blue-100 mb-2">Cara Paling Mudah</div>
    <h2 class="text-xl font-extrabold tracking-tight">Mulai dari stok bahan dulu</h2>
    <p class="text-sm text-blue-50 mt-2 max-w-3xl">
        Di halaman ini kamu cukup isi bahan yang kamu punya. Belum perlu hitung porsi. Misalnya:
        <span class="font-semibold">Ayam Fillet 3000 g</span>,
        <span class="font-semibold">Tepung 1200 g</span>,
        <span class="font-semibold">Susu 500 ml</span>.
        Setelah itu baru masuk ke halaman resep untuk bilang 1 porsi butuh berapa gram atau ml.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 1</div>
        <div class="text-base font-bold text-slate-900">Tambah nama bahan</div>
        <div class="text-sm text-slate-500 mt-2">Contoh: Daging Ayam Fillet, Tepung Bumbu, Susu Cair.</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 2</div>
        <div class="text-base font-bold text-slate-900">Isi stok yang kamu punya</div>
        <div class="text-sm text-slate-500 mt-2">Contoh: ayam 3000 g. Ini artinya stok bahan mentah, bukan stok menu.</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 3</div>
        <div class="text-base font-bold text-slate-900">Isi batas minimum</div>
        <div class="text-sm text-slate-500 mt-2">Contoh: ayam minimal 1000 g. Kalau stok turun sampai sini, status akan berubah jadi restock.</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Bahan</div>
        <div class="text-[28px] font-extrabold text-slate-900">{{ $ingredients->count() }}</div>
        <div class="text-[12px] text-slate-400 mt-1">Jumlah item bahan baku yang tersimpan.</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Stok Menipis</div>
        <div class="text-[28px] font-extrabold text-red-600">{{ $lowStockCount }}</div>
        <div class="text-[12px] text-slate-400 mt-1">Bahan yang stoknya sudah menyentuh batas minimum.</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Satuan Rekomendasi</div>
        <div class="text-[20px] font-extrabold text-slate-900">g, ml, pcs</div>
        <div class="text-[12px] text-slate-400 mt-1">Gunakan unit se-konsisten mungkin agar hitungan resep tetap akurat.</div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 lg:px-6 border-b border-slate-200">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Bahan Baku</h2>
            <p class="text-xs text-slate-400 mt-1">Kelola stok bahan yang akan dipakai oleh resep tiap menu.</p>
        </div>
        <button type="button" onclick="openIngredientModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[13px] font-semibold transition-colors shadow-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Bahan
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                    <th class="py-3 px-6">Nama</th>
                    <th class="py-3 px-6">Stok Saat Ini</th>
                    <th class="py-3 px-6">Batas Minimum</th>
                    <th class="py-3 px-6">Status</th>
                    <th class="py-3 px-6">Catatan</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                @forelse($ingredients as $ingredient)
                    @php($isLowStock = $ingredient->stock_quantity <= $ingredient->minimum_stock_quantity)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="font-semibold text-slate-900">{{ $ingredient->name }}</div>
                            <div class="text-[11px] text-slate-400 uppercase">{{ $ingredient->unit }}</div>
                        </td>
                        <td class="py-3.5 px-6 font-semibold text-slate-900">{{ rtrim(rtrim(number_format($ingredient->stock_quantity, 2, '.', ''), '0'), '.') }} {{ $ingredient->unit }}</td>
                        <td class="py-3.5 px-6">{{ rtrim(rtrim(number_format($ingredient->minimum_stock_quantity, 2, '.', ''), '0'), '.') }} {{ $ingredient->unit }}</td>
                        <td class="py-3.5 px-6">
                            @if($isLowStock)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 border border-red-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Restock
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-600 border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Aman
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-slate-500">{{ $ingredient->notes ?: '-' }}</td>
                        <td class="py-3.5 px-6">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-600 hover:text-white rounded-md transition-colors"
                                    title="Edit"
                                    onclick="openEditIngredientModal(
                                        {{ $ingredient->id }},
                                        @js($ingredient->name),
                                        @js($ingredient->unit),
                                        @js($ingredient->stock_quantity),
                                        @js($ingredient->minimum_stock_quantity),
                                        @js($ingredient->notes)
                                    )"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('admin.ingredients.destroy', $ingredient) }}" method="POST" onsubmit="return confirm('Hapus bahan baku ini?');">
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
                        <td colspan="6" class="text-center py-10 text-slate-400">Belum ada bahan baku. Tambahkan satu bahan untuk mulai mengatur stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="ingredientModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeIngredientModal()"></div>
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="px-6 pt-6 pb-5 border-b border-slate-100">
                <h3 id="ingredientModalTitle" class="text-lg font-bold text-slate-900">Tambah Bahan Baku</h3>
                <p class="text-sm text-slate-400 mt-1">Isi stok bahan mentah yang kamu punya sekarang, misalnya ayam 3000 g.</p>
            </div>
            <form id="ingredientForm" method="POST" action="{{ route('admin.ingredients.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" id="ingredientMethod" name="_method" value="POST">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Nama Bahan</label>
                    <input type="text" id="ingredientName" name="name" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Satuan</label>
                        <input type="text" id="ingredientUnit" name="unit" required placeholder="g / ml / pcs" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        <p class="mt-1 text-xs text-slate-400">Paling aman pakai `g`, `ml`, atau `pcs`.</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Stok Saat Ini</label>
                        <input type="number" id="ingredientStock" name="stock_quantity" min="0" step="0.01" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        <p class="mt-1 text-xs text-slate-400">Contoh: 3000 jika stok ayam 3 kg dan satuannya `g`.</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Batas Minimum</label>
                        <input type="number" id="ingredientMinimumStock" name="minimum_stock_quantity" min="0" step="0.01" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        <p class="mt-1 text-xs text-slate-400">Kalau stok turun sampai angka ini, status berubah jadi restock.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Catatan</label>
                    <input type="text" id="ingredientNotes" name="notes" placeholder="Contoh: simpan di freezer" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                </div>
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" onclick="closeIngredientModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const ingredientModal = document.getElementById('ingredientModal');
    const ingredientForm = document.getElementById('ingredientForm');
    const ingredientModalTitle = document.getElementById('ingredientModalTitle');
    const ingredientMethod = document.getElementById('ingredientMethod');

    function openIngredientModal() {
        ingredientModalTitle.textContent = 'Tambah Bahan Baku';
        ingredientForm.action = @js(route('admin.ingredients.store'));
        ingredientMethod.value = 'POST';
        ingredientForm.reset();
        ingredientModal.classList.remove('hidden');
    }

    function openEditIngredientModal(id, name, unit, stockQuantity, minimumStockQuantity, notes) {
        ingredientModalTitle.textContent = 'Edit Bahan Baku';
        ingredientForm.action = `/admin/bahan-baku/${id}`;
        ingredientMethod.value = 'PUT';
        document.getElementById('ingredientName').value = name ?? '';
        document.getElementById('ingredientUnit').value = unit ?? '';
        document.getElementById('ingredientStock').value = stockQuantity ?? 0;
        document.getElementById('ingredientMinimumStock').value = minimumStockQuantity ?? 0;
        document.getElementById('ingredientNotes').value = notes ?? '';
        ingredientModal.classList.remove('hidden');
    }

    function closeIngredientModal() {
        ingredientModal.classList.add('hidden');
    }
</script>
@endsection
