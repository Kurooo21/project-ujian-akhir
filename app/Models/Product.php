<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'badge',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
