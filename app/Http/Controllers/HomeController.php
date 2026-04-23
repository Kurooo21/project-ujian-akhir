<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Banner;
use App\Models\Outlet;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if (Auth::user()->role === 'kasir') {
                return redirect()->route('kasir.dashboard');
            }
        }

        $products = Product::with('reviews.user')->get();
        $banners = Banner::all();
        $settings = Setting::pluck('value', 'key');
        $outlets = Outlet::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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

        $outletsData = $outlets->map(function ($outlet) {
            return [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'province' => $outlet->province,
                'city' => $outlet->city,
                'district' => $outlet->district,
                'address' => $outlet->address,
                'phone' => $outlet->phone,
                'maps_url' => $outlet->maps_url,
                'label' => trim($outlet->name . ' - ' . collect([$outlet->district, $outlet->city])->filter()->implode(', '), ' -'),
            ];
        })->values()->toArray();

        return view('home', compact('products', 'productsData', 'banners', 'settings', 'outletsData'));
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
