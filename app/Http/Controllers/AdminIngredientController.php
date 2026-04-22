<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminIngredientController extends Controller
{
    public function index(): View
    {
        $ingredients = Ingredient::query()
            ->orderBy('name')
            ->get();

        $lowStockCount = $ingredients
            ->filter(fn (Ingredient $ingredient) => $ingredient->stock_quantity <= $ingredient->minimum_stock_quantity)
            ->count();

        return view('admin.ingredients', compact('ingredients', 'lowStockCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'stock_quantity' => 'required|numeric|min:0',
            'minimum_stock_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        Ingredient::create($validated);

        return redirect()
            ->route('admin.ingredients')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'stock_quantity' => 'required|numeric|min:0',
            'minimum_stock_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $ingredient->update($validated);

        return redirect()
            ->route('admin.ingredients')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->recipeItems()->exists()) {
            return redirect()
                ->route('admin.ingredients')
                ->with('error', 'Bahan baku masih dipakai di resep. Hapus relasi resepnya dulu.');
        }

        $ingredient->delete();

        return redirect()
            ->route('admin.ingredients')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
