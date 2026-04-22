@extends('admin.layouts.app')
@section('page_title', 'Resep Produk')

@section('content')
<div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-2xl p-6 shadow-sm mb-6">
    <div class="text-[11px] font-semibold uppercase tracking-widest text-amber-100 mb-2">Konsep Sederhana</div>
    <h2 class="text-xl font-extrabold tracking-tight">Resep per porsi = bahan yang dipakai untuk 1 menu</h2>
    <p class="text-sm text-amber-50 mt-2 max-w-3xl">
        Contoh paling gampang:
        stok ayam <span class="font-semibold">3000 g</span>,
        resep ayam per porsi <span class="font-semibold">300 g</span>.
        Berarti dari ayam saja kamu bisa bikin
        <span class="font-semibold">10 porsi</span>.
        Sistem akan membandingkan semua bahan, lalu mengambil angka paling kecil sebagai stok menu.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 1</div>
        <div class="text-base font-bold text-slate-900">Pilih produknya</div>
        <div class="text-sm text-slate-500 mt-2">Misalnya Ayam Fillet Crispy.</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 2</div>
        <div class="text-base font-bold text-slate-900">Isi bahan untuk 1 porsi</div>
        <div class="text-sm text-slate-500 mt-2">Contoh: ayam 300 g, tepung 120 g, susu 50 ml.</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Langkah 3</div>
        <div class="text-base font-bold text-slate-900">Lihat hasil otomatis</div>
        <div class="text-sm text-slate-500 mt-2">Sistem akan menghitung berapa porsi yang masih bisa dijual.</div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[320px_minmax(0,1fr)] gap-6">
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Pilih Produk</h2>
                <p class="text-xs text-slate-400 mt-1">Tentukan menu yang ingin kamu isi resep per porsinya.</p>
            </div>
            <div class="p-5">
                @if($products->isEmpty())
                    <div class="text-sm text-slate-500">Belum ada produk. Tambahkan produk dulu sebelum mengatur resep.</div>
                @else
                    <form method="GET" action="{{ route('admin.recipes') }}" class="space-y-3">
                        <select name="product" onchange="this.form.submit()" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5 bg-white">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(optional($selectedProduct)->id === $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.produk') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                            <span>Lihat daftar produk</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Ringkasan Stok</h2>
                <p class="text-xs text-slate-400 mt-1">Porsi tersedia dihitung dari bahan yang paling cepat habis.</p>
            </div>
            <div class="p-5">
                @if(!$selectedProduct)
                    <div class="text-sm text-slate-500">Pilih produk untuk melihat ringkasan resep.</div>
                @elseif(!$selectedProduct->inventory_snapshot)
                    <div class="text-sm text-slate-500">Produk ini belum punya resep. Tambahkan bahan satu per satu di panel kanan.</div>
                @else
                    @php($snapshot = $selectedProduct->inventory_snapshot)
                    <div class="space-y-4">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Produk</div>
                            <div class="text-lg font-bold text-slate-900">{{ $selectedProduct->name }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                                <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Porsi Tersedia</div>
                                <div class="text-2xl font-extrabold text-slate-900">{{ $snapshot['available_portions'] }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                                <div class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Batas Minimum</div>
                                <div class="text-2xl font-extrabold text-slate-900">{{ $snapshot['minimum_portions'] }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-slate-500">
                            Bahan pembatas saat ini:
                            <span class="font-semibold text-slate-900">{{ $snapshot['limiting_ingredient_name'] }}</span>
                        </div>
                        <div>
                            @if($snapshot['is_low_stock'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Kurang {{ $snapshot['shortage_portions'] }} porsi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Stok aman
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 lg:px-6 border-b border-slate-200">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Komposisi Resep</h2>
                <p class="text-xs text-slate-400 mt-1">
                    @if($selectedProduct)
                        Atur kebutuhan bahan untuk 1 porsi <span class="font-semibold text-slate-600">{{ $selectedProduct->name }}</span>.
                    @else
                        Pilih produk dulu untuk mulai menyusun resep.
                    @endif
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[13px] font-semibold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                onclick="openRecipeModal()"
                @disabled(!$selectedProduct || $ingredients->isEmpty())
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Bahan ke Resep
            </button>
        </div>

        @if($selectedProduct && $ingredients->isEmpty())
            <div class="px-6 pt-6">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Tambahkan minimal satu bahan baku dulu di halaman <a href="{{ route('admin.ingredients') }}" class="font-semibold underline">Bahan Baku</a>.
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-400 uppercase tracking-widest text-left">
                        <th class="py-3 px-6">Bahan</th>
                        <th class="py-3 px-6">Pakai / 1 Porsi</th>
                        <th class="py-3 px-6">Stok Tersedia</th>
                        <th class="py-3 px-6">Bisa Jadi</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-slate-600 divide-y divide-slate-100">
                    @forelse($recipeItems as $recipeItem)
                        @php($ingredient = $recipeItem->ingredient)
                        @php($portionCapacity = $ingredient && $recipeItem->quantity_required > 0 ? floor($ingredient->stock_quantity / $recipeItem->quantity_required) : 0)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="font-semibold text-slate-900">{{ $ingredient?->name }}</div>
                                <div class="text-[11px] text-slate-400 uppercase">{{ $ingredient?->unit }}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="font-semibold text-slate-900">{{ rtrim(rtrim(number_format($recipeItem->quantity_required, 2, '.', ''), '0'), '.') }} {{ $ingredient?->unit }}</div>
                                <div class="text-[11px] text-slate-400">{{ $recipeItem->display_quantity ?: 'Tanpa label tambahan' }}</div>
                            </td>
                            <td class="py-3.5 px-6">{{ $ingredient ? rtrim(rtrim(number_format($ingredient->stock_quantity, 2, '.', ''), '0'), '.') . ' ' . $ingredient->unit : '-' }}</td>
                            <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $portionCapacity }} porsi</td>
                            <td class="py-3.5 px-6">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-600 hover:text-white rounded-md transition-colors"
                                        title="Edit"
                                        onclick="openEditRecipeModal(
                                            {{ $recipeItem->id }},
                                            {{ $recipeItem->product_id }},
                                            {{ $recipeItem->ingredient_id }},
                                            @js($recipeItem->quantity_required),
                                            @js($recipeItem->display_quantity)
                                        )"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.recipes.destroy', $recipeItem) }}" method="POST" onsubmit="return confirm('Hapus bahan ini dari resep?');">
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
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                @if($selectedProduct)
                                    Belum ada bahan untuk resep produk ini.
                                @else
                                    Pilih produk terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="recipeModal" class="fixed inset-0 z-[2004] hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeRecipeModal()"></div>
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="px-6 pt-6 pb-5 border-b border-slate-100">
                <h3 id="recipeModalTitle" class="text-lg font-bold text-slate-900">Tambah Bahan ke Resep</h3>
                <p class="text-sm text-slate-400 mt-1">Isi berapa banyak bahan yang dipakai untuk membuat 1 porsi menu.</p>
            </div>
            <form id="recipeForm" method="POST" action="{{ route('admin.recipes.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" id="recipeMethod" name="_method" value="POST">
                <input type="hidden" id="recipeProductId" name="product_id" value="{{ optional($selectedProduct)->id }}">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Bahan Baku</label>
                    <select id="recipeIngredientId" name="ingredient_id" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5 bg-white">
                        <option value="">Pilih bahan</option>
                        @foreach($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Pakai per 1 Porsi</label>
                        <input type="number" id="recipeQuantityRequired" name="quantity_required" min="0.01" step="0.01" required class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        <p class="mt-1 text-xs text-slate-400">Contoh: ayam 300 jika 1 porsi butuh 300 g.</p>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Label Tampilan</label>
                        <input type="text" id="recipeDisplayQuantity" name="display_quantity" placeholder="Contoh: 300 gr atau 2 siung" class="block w-full rounded-md border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border p-2.5">
                        <p class="mt-1 text-xs text-slate-400">Ini hanya membantu kamu membaca resep dengan lebih nyaman.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" onclick="closeRecipeModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const recipeModal = document.getElementById('recipeModal');
    const recipeForm = document.getElementById('recipeForm');
    const recipeModalTitle = document.getElementById('recipeModalTitle');
    const recipeMethod = document.getElementById('recipeMethod');
    const recipeProductId = document.getElementById('recipeProductId');
    const selectedProductId = @js(optional($selectedProduct)->id);

    function openRecipeModal() {
        if (!selectedProductId) {
            return;
        }

        recipeModalTitle.textContent = 'Tambah Bahan ke Resep';
        recipeForm.action = @js(route('admin.recipes.store'));
        recipeMethod.value = 'POST';
        recipeForm.reset();
        recipeProductId.value = selectedProductId;
        recipeModal.classList.remove('hidden');
    }

    function openEditRecipeModal(id, productId, ingredientId, quantityRequired, displayQuantity) {
        recipeModalTitle.textContent = 'Edit Bahan Resep';
        recipeForm.action = `/admin/resep/${id}`;
        recipeMethod.value = 'PUT';
        recipeProductId.value = productId;
        document.getElementById('recipeIngredientId').value = ingredientId;
        document.getElementById('recipeQuantityRequired').value = quantityRequired ?? '';
        document.getElementById('recipeDisplayQuantity').value = displayQuantity ?? '';
        recipeModal.classList.remove('hidden');
    }

    function closeRecipeModal() {
        recipeModal.classList.add('hidden');
    }
</script>
@endsection
