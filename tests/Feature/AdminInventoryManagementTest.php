<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Pesanan;
use App\Models\Product;
use App\Models\RecipeItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_payment_reduces_ingredient_stock_based_on_recipe(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'confirm-payment-admin@example.com',
            'username' => 'confirm-payment-admin',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Ayam Fillet Crispy',
            'price' => 25000,
            'description' => 'Menu ayam crispy.',
            'image' => 'asset/logo merah.png',
            'category' => 'makanan',
            'minimum_portions' => 5,
        ]);

        $ayam = Ingredient::create([
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 3000,
            'minimum_stock_quantity' => 1000,
        ]);

        $tepung = Ingredient::create([
            'name' => 'Tepung Bumbu',
            'unit' => 'g',
            'stock_quantity' => 1200,
            'minimum_stock_quantity' => 400,
        ]);

        RecipeItem::create([
            'product_id' => $product->id,
            'ingredient_id' => $ayam->id,
            'quantity_required' => 300,
            'display_quantity' => '300 gr',
        ]);

        RecipeItem::create([
            'product_id' => $product->id,
            'ingredient_id' => $tepung->id,
            'quantity_required' => 120,
            'display_quantity' => '120 gr',
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-INV-001',
            'nama_pelanggan' => 'Faa',
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
            'pesanan' => $product->name,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'total_harga' => 50000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'qris',
            'payment_status' => 'Menunggu Pembayaran',
            'status' => 'Menunggu Pembayaran',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pesanan.confirm-payment'), [
            'group_id' => 'ORD-INV-001',
        ]);

        $response->assertRedirect(route('admin.pesanan'));
        $this->assertDatabaseHas('ingredients', [
            'id' => $ayam->id,
            'stock_quantity' => 2400,
        ]);
        $this->assertDatabaseHas('ingredients', [
            'id' => $tepung->id,
            'stock_quantity' => 960,
        ]);
        $this->assertDatabaseHas('pesanan', [
            'order_code' => 'ORD-INV-001',
            'payment_status' => 'Lunas',
            'status' => 'Diproses',
        ]);
    }

    public function test_confirm_payment_is_blocked_when_ingredient_stock_is_not_enough(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'confirm-payment-stock@example.com',
            'username' => 'confirm-payment-stock',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Ayam Fillet Crispy',
            'price' => 25000,
            'description' => 'Menu ayam crispy.',
            'image' => 'asset/logo merah.png',
            'category' => 'makanan',
            'minimum_portions' => 5,
        ]);

        $ayam = Ingredient::create([
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 400,
            'minimum_stock_quantity' => 100,
        ]);

        RecipeItem::create([
            'product_id' => $product->id,
            'ingredient_id' => $ayam->id,
            'quantity_required' => 300,
            'display_quantity' => '300 gr',
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-INV-002',
            'nama_pelanggan' => 'Faa',
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
            'pesanan' => $product->name,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'total_harga' => 50000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'qris',
            'payment_status' => 'Menunggu Pembayaran',
            'status' => 'Menunggu Pembayaran',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.pesanan'))->put(route('admin.pesanan.confirm-payment'), [
            'group_id' => 'ORD-INV-002',
        ]);

        $response->assertRedirect(route('admin.pesanan'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ingredients', [
            'id' => $ayam->id,
            'stock_quantity' => 400,
        ]);
        $this->assertDatabaseHas('pesanan', [
            'order_code' => 'ORD-INV-002',
            'payment_status' => 'Menunggu Pembayaran',
            'status' => 'Menunggu Pembayaran',
        ]);
    }
}
