<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Chi-Pok',
                'email' => 'admin@chipok.test',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        $sampleOutlets = Outlet::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(2)
            ->get();

        foreach ($sampleOutlets as $index => $outlet) {
            User::updateOrCreate(
                ['username' => 'kasir' . ($index + 1)],
                [
                    'name' => 'Kasir ' . $outlet->name,
                    'email' => 'kasir' . ($index + 1) . '@chipok.test',
                    'password' => Hash::make('kasir123'),
                    'role' => 'kasir',
                    'outlet_id' => $outlet->id,
                ]
            );
        }
    }
}
