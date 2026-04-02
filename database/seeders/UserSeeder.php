<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya seed akun admin - user/pelanggan membuat akun sendiri via register
        User::firstOrCreate(
            ['username' => 'admin'],  // Cari berdasarkan username
            [
                'name' => 'Admin Chi-Pok',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
    }
}
