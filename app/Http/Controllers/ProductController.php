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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category' => 'nullable|string|in:makanan,minuman',
            'minimum_portions' => 'nullable|integer|min:0|max:1000',
        ]);

        // Handle upload gambar
        $imagePath = 'asset/logo merah.png'; // Default jika tidak upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            // Simpan ke public/asset/menu/
            $file->move(public_path('asset/menu'), $filename);
            $imagePath = 'asset/menu/' . $filename;
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'category' => $request->category ?: 'makanan',
            'minimum_portions' => $request->filled('minimum_portions')
                ? $request->integer('minimum_portions')
                : 5,
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
            'minimum_portions' => 'nullable|integer|min:0|max:1000',
            'category' => 'nullable|string|in:makanan,minuman',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('minimum_portions')) $product->minimum_portions = $request->integer('minimum_portions');
        if ($request->has('category')) $product->category = $request->category;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->move(public_path('asset/menu'), $filename);
            $product->image = 'asset/menu/' . $filename;
        }

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
