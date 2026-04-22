<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class AdminProdukController extends Controller
{
    public function index()
    {
        $products = Product::with('recipeItems.ingredient')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.produk', compact('products'));
    }
}
