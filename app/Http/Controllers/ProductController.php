<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'nullable|string',
            'category' => 'nullable|string|in:makanan,minuman',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $request->image ?: 'asset/logo merah.png',
            'category' => $request->category ?: 'makanan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan!',
            'product' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'price' => 'nullable|numeric|min:0',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('description')) $product->description = $request->description;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diubah!',
            'product' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus!'
        ]);
    }
}
