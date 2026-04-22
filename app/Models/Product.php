<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected ?array $inventorySnapshotCache = null;

    protected bool $inventorySnapshotResolved = false;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'badge',
        'category',
        'minimum_portions',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'minimum_portions' => 'integer',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getInventorySnapshotAttribute(): ?array
    {
        return $this->resolveInventorySnapshot();
    }

    public function getAvailablePortionsAttribute(): ?int
    {
        return $this->resolveInventorySnapshot()['available_portions'] ?? null;
    }

    public function getStockShortagePortionsAttribute(): ?int
    {
        return $this->resolveInventorySnapshot()['shortage_portions'] ?? null;
    }

    public function getLimitingIngredientNameAttribute(): ?string
    {
        return $this->resolveInventorySnapshot()['limiting_ingredient_name'] ?? null;
    }

    public function getIsLowStockAttribute(): bool
    {
        return (bool) ($this->resolveInventorySnapshot()['is_low_stock'] ?? false);
    }

    public function getHasRecipeAttribute(): bool
    {
        return $this->resolveInventorySnapshot() !== null;
    }

    protected function resolveInventorySnapshot(): ?array
    {
        if ($this->inventorySnapshotResolved) {
            return $this->inventorySnapshotCache;
        }

        $this->inventorySnapshotResolved = true;
        $this->loadMissing('recipeItems.ingredient');

        $ingredientSnapshots = $this->recipeItems
            ->filter(fn (RecipeItem $item) => $item->ingredient && $item->quantity_required > 0)
            ->map(function (RecipeItem $item) {
                $stockQuantity = max((float) $item->ingredient->stock_quantity, 0);
                $requiredQuantity = (float) $item->quantity_required;

                return [
                    'ingredient_name' => $item->ingredient->name,
                    'available_portions' => (int) floor($stockQuantity / $requiredQuantity),
                ];
            })
            ->values();

        if ($ingredientSnapshots->isEmpty()) {
            return $this->inventorySnapshotCache = null;
        }

        $limitingIngredient = $ingredientSnapshots
            ->sortBy('available_portions')
            ->first();

        $availablePortions = (int) $ingredientSnapshots->min('available_portions');
        $minimumPortions = max((int) ($this->minimum_portions ?? 0), 0);

        return $this->inventorySnapshotCache = [
            'available_portions' => $availablePortions,
            'minimum_portions' => $minimumPortions,
            'shortage_portions' => max($minimumPortions - $availablePortions, 0),
            'is_low_stock' => $availablePortions <= $minimumPortions,
            'limiting_ingredient_name' => $limitingIngredient['ingredient_name'] ?? null,
        ];
    }
}
