<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => "CHI'POK LAVA 🔥",
                'price' => 27000,
                'description' => 'Pedas & melimpah, sensasi lava cabe!',
                'image' => 'asset/rasa lava.png',
                'badge' => 'BEST SELLER',
            ],
            [
                'name' => "CHI'POK ORI",
                'price' => 25000,
                'description' => 'Gurih & renyah klasic favorit!',
                'image' => 'asset/original.png',
                'badge' => null,
            ],
            [
                'name' => "CHI'POK KEJU",
                'price' => 26000,
                'description' => 'Kriuk, gurih keju melimpah!',
                'image' => 'asset/rasa keju.png',
                'badge' => null,
            ],
            [
                'name' => "CHI'POK BUMBU",
                'price' => 24000,
                'description' => 'Rasa kaya rempah, unik!',
                'image' => 'asset/rasa bumbu.png',
                'badge' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
