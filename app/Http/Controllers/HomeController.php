<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('reviews.user')->get();

        // Transform for JS consumption
        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'desc' => $product->description,
                'image' => $product->image,
                'badge' => $product->badge,
                'reviews' => $product->reviews->map(function ($review) {
                    return [
                        'user' => $review->user->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'date' => $review->created_at->format('d/m/Y'),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return view('home', compact('products', 'productsData'));
    }
}

