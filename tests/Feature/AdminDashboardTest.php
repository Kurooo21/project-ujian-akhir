<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Pesanan;
use App\Models\Product;
use App\Models\RecipeItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_groups_orders_and_excludes_cancelled_orders_from_summary(): void
    {
        config(['app.dashboard_timezone' => 'Asia/Jakarta']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $today = Carbon::now('Asia/Jakarta')->subHour();
        $expectedDateLabel = $today->copy()->locale('id')->translatedFormat('d F Y');

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-000001',
            'nama_pelanggan' => 'Faa',
            'no_hp' => '085678906',
            'alamat' => 'Jakarta',
            'pesanan' => 'PEDAS POKPOK',
            'jumlah' => 1,
            'harga_satuan' => 30000,
            'total_harga' => 30000,
            'jenis_belanja' => 'Dine In',
            'payment_method' => 'whatsapp_transfer',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-000001',
            'nama_pelanggan' => 'Faa',
            'no_hp' => '085678906',
            'alamat' => 'Jakarta',
            'pesanan' => 'PEDAS POKPOK',
            'jumlah' => 2,
            'harga_satuan' => 15000,
            'total_harga' => 30000,
            'jenis_belanja' => 'Dine In',
            'payment_method' => 'whatsapp_transfer',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-000002',
            'nama_pelanggan' => 'Faa',
            'no_hp' => '085678906',
            'alamat' => 'Jakarta',
            'pesanan' => 'ORIGINAL POKPOK',
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'total_harga' => 25000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'whatsapp_transfer',
            'payment_status' => 'Lunas',
            'status' => 'Dibatalkan',
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('summaryBadge', 'Hari Ini');
        $response->assertViewHas('summaryDateLabel', $expectedDateLabel);
        $response->assertViewHas('totalPendapatan', 60000);
        $response->assertViewHas('jumlahTransaksi', 1);
        $response->assertViewHas('stokTipis', null);
    }

    public function test_dashboard_counts_low_stock_products_based_on_recipe_portions(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'stock-admin@example.com',
            'username' => 'stock-admin',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $produkStokTipis = Product::create([
            'name' => 'Ayam Fillet Crispy',
            'price' => 25000,
            'description' => 'Menu ayam crispy.',
            'image' => 'asset/logo merah.png',
            'category' => 'makanan',
            'minimum_portions' => 5,
        ]);

        $produkAman = Product::create([
            'name' => 'Ayam Fillet Jumbo',
            'price' => 32000,
            'description' => 'Menu ayam jumbo.',
            'image' => 'asset/logo merah.png',
            'category' => 'makanan',
            'minimum_portions' => 2,
        ]);

        $ayam = Ingredient::create([
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 900,
        ]);

        $tepung = Ingredient::create([
            'name' => 'Tepung Bumbu',
            'unit' => 'g',
            'stock_quantity' => 840,
        ]);

        $susu = Ingredient::create([
            'name' => 'Susu Cair',
            'unit' => 'ml',
            'stock_quantity' => 500,
        ]);

        RecipeItem::create([
            'product_id' => $produkStokTipis->id,
            'ingredient_id' => $ayam->id,
            'quantity_required' => 300,
            'display_quantity' => '300 gr',
        ]);

        RecipeItem::create([
            'product_id' => $produkStokTipis->id,
            'ingredient_id' => $tepung->id,
            'quantity_required' => 120,
            'display_quantity' => '120 gr',
        ]);

        RecipeItem::create([
            'product_id' => $produkStokTipis->id,
            'ingredient_id' => $susu->id,
            'quantity_required' => 50,
            'display_quantity' => '50 ml',
        ]);

        RecipeItem::create([
            'product_id' => $produkAman->id,
            'ingredient_id' => $ayam->id,
            'quantity_required' => 150,
            'display_quantity' => '150 gr',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('stokTipis', 1);
        $response->assertViewHas('stokTipisDescription', 'Jumlah menu dengan porsi tersedia di bawah batas minimum.');
    }
}
