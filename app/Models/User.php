<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'alamat',
        'no_hp',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
