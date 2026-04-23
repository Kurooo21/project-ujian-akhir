<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    protected $fillable = [
        'name',
        'province',
        'city',
        'district',
        'address',
        'phone',
        'maps_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getAreaLabelAttribute(): string
    {
        return collect([$this->district, $this->city, $this->province])
            ->filter()
            ->implode(', ');
    }
}
