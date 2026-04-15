<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Jika user yang login adalah admin, redirect ke dashboard admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $products = Product::with('reviews.user')->get();
        $banners = Banner::all();
        $settings = Setting::pluck('value', 'key');

        // Transform for JS consumption
        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'desc' => $product->description,
                'image' => $product->image,
                'badge' => $product->badge,
                'category' => $product->category ?? 'makanan',
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

        return view('home', compact('products', 'productsData', 'banners', 'settings'));
    }

    public function menu()
    {
        $products = Product::with('reviews.user')->get();

        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'desc' => $product->description,
                'image' => $product->image,
                'badge' => $product->badge,
                'category' => $product->category ?? 'makanan',
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

        return view('menu', compact('products', 'productsData'));
    }
}
