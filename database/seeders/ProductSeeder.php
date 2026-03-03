<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // === MAKANAN ===
            [
                'name' => "CHI'POK LAVA 🔥",
                'price' => 27000,
                'description' => 'Pedas & melimpah, sensasi lava cabe!',
                'image' => 'asset/rasa lava.png',
                'badge' => 'BEST SELLER',
                'category' => 'makanan',
            ],
            [
                'name' => "CHI'POK ORI",
                'price' => 25000,
                'description' => 'Gurih & renyah klasic favorit!',
                'image' => 'asset/original.png',
                'badge' => null,
                'category' => 'makanan',
            ],
            [
                'name' => "CHI'POK KEJU",
                'price' => 26000,
                'description' => 'Kriuk, gurih keju melimpah!',
                'image' => 'asset/rasa keju.png',
                'badge' => null,
                'category' => 'makanan',
            ],
            [
                'name' => "CHI'POK BUMBU",
                'price' => 24000,
                'description' => 'Rasa kaya rempah, unik!',
                'image' => 'asset/rasa bumbu.png',
                'badge' => null,
                'category' => 'makanan',
            ],

            // === MINUMAN ===
            [
                'name' => 'Es Teh Manis',
                'price' => 8000,
                'description' => 'Teh manis segar dengan es batu.',
                'image' => 'asset/logo merah.png',
                'badge' => null,
                'category' => 'minuman',
            ],
            [
                'name' => 'Es Jeruk Segar',
                'price' => 10000,
                'description' => 'Jeruk peras asli, segar & nikmat!',
                'image' => 'asset/logo merah.png',
                'badge' => 'FAVORIT',
                'category' => 'minuman',
            ],
            [
                'name' => 'Lemon Tea',
                'price' => 12000,
                'description' => 'Perpaduan teh & lemon yang menyegarkan.',
                'image' => 'asset/logo merah.png',
                'badge' => null,
                'category' => 'minuman',
            ],
            [
                'name' => 'Milkshake Coklat',
                'price' => 18000,
                'description' => 'Creamy, manis, & coklat yang kaya rasa.',
                'image' => 'asset/logo merah.png',
                'badge' => 'NEW',
                'category' => 'minuman',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
