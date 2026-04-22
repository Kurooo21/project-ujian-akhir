<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            [
                'name' => 'Chi-Pok Kemanggisan',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Barat',
                'district' => 'Kemanggisan',
                'address' => 'Jl. Kemanggisan Raya No. 21, Palmerah, Jakarta Barat',
                'phone' => '0812-8000-1101',
                'maps_url' => 'https://maps.google.com/?q=Jl.+Kemanggisan+Raya+No.+21+Jakarta+Barat',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Chi-Pok Tebet',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Tebet',
                'address' => 'Jl. Tebet Timur Dalam VIII No. 9, Tebet, Jakarta Selatan',
                'phone' => '0812-8000-1102',
                'maps_url' => 'https://maps.google.com/?q=Jl.+Tebet+Timur+Dalam+VIII+No.+9+Jakarta+Selatan',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Chi-Pok Antapani',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Antapani',
                'address' => 'Jl. Terusan Jakarta No. 188, Antapani, Bandung',
                'phone' => '0812-8000-1103',
                'maps_url' => 'https://maps.google.com/?q=Jl.+Terusan+Jakarta+No.+188+Bandung',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Chi-Pok Summarecon',
                'province' => 'Jawa Barat',
                'city' => 'Bekasi',
                'district' => 'Summarecon',
                'address' => 'Jl. Bulevar Ahmad Yani Blok M, Summarecon Bekasi',
                'phone' => '0812-8000-1104',
                'maps_url' => 'https://maps.google.com/?q=Summarecon+Bekasi',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($outlets as $outlet) {
            Outlet::updateOrCreate(
                [
                    'name' => $outlet['name'],
                    'city' => $outlet['city'],
                ],
                $outlet
            );
        }
    }
}
