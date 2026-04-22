<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_inventory_management_pages(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'inventory-pages@example.com',
            'username' => 'inventory-pages',
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

        $this->actingAs($admin)->get(route('admin.ingredients'))
            ->assertOk()
            ->assertSee('Daftar Bahan Baku');

        $this->actingAs($admin)->get(route('admin.recipes', ['product' => $product->id]))
            ->assertOk()
            ->assertSee('Komposisi Resep');
    }

    public function test_admin_can_create_ingredient_from_inventory_page(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'inventory-admin@example.com',
            'username' => 'inventory-admin',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ingredients.store'), [
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 3000,
            'minimum_stock_quantity' => 1000,
            'notes' => 'Simpan di freezer',
        ]);

        $response->assertRedirect(route('admin.ingredients'));
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 3000,
            'minimum_stock_quantity' => 1000,
        ]);
    }

    public function test_admin_can_create_recipe_item_from_recipe_page(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'recipe-admin@example.com',
            'username' => 'recipe-admin',
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

        $ingredient = Ingredient::create([
            'name' => 'Daging Ayam Fillet',
            'unit' => 'g',
            'stock_quantity' => 3000,
            'minimum_stock_quantity' => 1000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.recipes.store'), [
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'quantity_required' => 300,
            'display_quantity' => '300 gr',
        ]);

        $response->assertRedirect(route('admin.recipes', ['product' => $product->id]));
        $this->assertDatabaseHas('recipe_items', [
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'quantity_required' => 300,
            'display_quantity' => '300 gr',
        ]);
    }
}
