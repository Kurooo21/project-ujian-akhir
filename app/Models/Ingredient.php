<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'minimum_stock_quantity',
        'notes',
    ];

    protected $casts = [
        'stock_quantity' => 'float',
        'minimum_stock_quantity' => 'float',
    ];

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }
}
