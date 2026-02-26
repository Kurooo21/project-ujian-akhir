<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Chi-Pok',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Pelanggan Setia',
            'username' => 'pelanggan',
            'password' => Hash::make('pelanggan123'),
            'role' => 'pelanggan',
        ]);
    }
}
