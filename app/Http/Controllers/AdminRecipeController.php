<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\RecipeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRecipeController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('recipeItems.ingredient')
            ->orderBy('name')
            ->get();

        $ingredients = Ingredient::query()
            ->orderBy('name')
            ->get();

        $selectedProduct = $products->firstWhere('id', $request->integer('product'))
            ?? $products->first();

        $recipeItems = $selectedProduct
            ? $selectedProduct->recipeItems
                ->sortBy(fn (RecipeItem $item) => $item->ingredient?->name ?? '')
                ->values()
            : collect();

        return view('admin.recipes', compact('products', 'ingredients', 'selectedProduct', 'recipeItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'ingredient_id' => [
                'required',
                'exists:ingredients,id',
                Rule::unique('recipe_items')->where(
                    fn ($query) => $query->where('product_id', $request->integer('product_id'))
                ),
            ],
            'quantity_required' => 'required|numeric|gt:0',
            'display_quantity' => 'nullable|string|max:50',
        ], [
            'ingredient_id.unique' => 'Bahan ini sudah ada di resep produk tersebut.',
        ]);

        RecipeItem::create($validated);

        return redirect()
            ->route('admin.recipes', ['product' => $request->integer('product_id')])
            ->with('success', 'Bahan resep berhasil ditambahkan.');
    }

    public function update(Request $request, RecipeItem $recipeItem): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'ingredient_id' => [
                'required',
                'exists:ingredients,id',
                Rule::unique('recipe_items')
                    ->ignore($recipeItem->id)
                    ->where(fn ($query) => $query->where('product_id', $request->integer('product_id'))),
            ],
            'quantity_required' => 'required|numeric|gt:0',
            'display_quantity' => 'nullable|string|max:50',
        ], [
            'ingredient_id.unique' => 'Bahan ini sudah ada di resep produk tersebut.',
        ]);

        $recipeItem->update($validated);

        return redirect()
            ->route('admin.recipes', ['product' => $request->integer('product_id')])
            ->with('success', 'Bahan resep berhasil diperbarui.');
    }

    public function destroy(RecipeItem $recipeItem): RedirectResponse
    {
        $productId = $recipeItem->product_id;
        $recipeItem->delete();

        return redirect()
            ->route('admin.recipes', ['product' => $productId])
            ->with('success', 'Bahan resep berhasil dihapus.');
    }
}
