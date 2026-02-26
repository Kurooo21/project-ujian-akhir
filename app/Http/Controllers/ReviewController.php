<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda!',
            'review' => [
                'user' => $review->user->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'date' => $review->created_at->format('d/m/Y'),
            ]
        ]);
    }

    public function show($productId)
    {
        $reviews = Review::with('user')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($r) {
                return [
                    'user' => $r->user->name,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'date' => $r->created_at->format('d/m/Y'),
                ];
            })
        ]);
    }
}
