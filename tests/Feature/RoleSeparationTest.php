<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_backoffice_users_to_their_own_dashboard(): void
    {
        $outlet = Outlet::create([
            'name' => 'Chi-Pok Antapani',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Antapani',
            'address' => 'Jl. Antapani No. 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-role@example.com',
            'username' => 'admin-role',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'name' => 'Kasir',
            'email' => 'kasir-role@example.com',
            'username' => 'kasir-role',
            'password' => bcrypt('secret'),
            'role' => 'kasir',
            'outlet_id' => $outlet->id,
        ]);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($kasir)
            ->get(route('home'))
            ->assertRedirect(route('kasir.dashboard'));
    }

    public function test_admin_and_kasir_routes_are_isolated(): void
    {
        $outlet = Outlet::create([
            'name' => 'Chi-Pok Antapani',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Antapani',
            'address' => 'Jl. Antapani No. 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-access@example.com',
            'username' => 'admin-access',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'name' => 'Kasir',
            'email' => 'kasir-access@example.com',
            'username' => 'kasir-access',
            'password' => bcrypt('secret'),
            'role' => 'kasir',
            'outlet_id' => $outlet->id,
        ]);

        $this->actingAs($admin)
            ->get(route('kasir.dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($kasir)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('kasir.dashboard'));
    }

    public function test_kasir_only_sees_orders_from_their_own_outlet(): void
    {
        $outletA = Outlet::create([
            'name' => 'Chi-Pok Antapani',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Antapani',
            'address' => 'Jl. Antapani No. 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $outletB = Outlet::create([
            'name' => 'Chi-Pok Tebet',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Tebet',
            'address' => 'Jl. Tebet No. 2',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $kasir = User::create([
            'name' => 'Kasir Antapani',
            'email' => 'kasir-filter@example.com',
            'username' => 'kasir-filter',
            'password' => bcrypt('secret'),
            'role' => 'kasir',
            'outlet_id' => $outletA->id,
        ]);

        Pesanan::create([
            'user_id' => $kasir->id,
            'order_code' => 'ORD-OUTLET-A',
            'outlet_id' => $outletA->id,
            'outlet_name' => $outletA->name,
            'outlet_city' => $outletA->city,
            'outlet_district' => $outletA->district,
            'outlet_address_snapshot' => $outletA->address,
            'nama_pelanggan' => 'Andi',
            'no_hp' => '0811111111',
            'alamat' => 'Bandung',
            'pesanan' => 'Ayam',
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'total_harga' => 25000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'qris',
            'payment_status' => 'Menunggu Pembayaran',
            'status' => 'Menunggu Pembayaran',
        ]);

        Pesanan::create([
            'user_id' => $kasir->id,
            'order_code' => 'ORD-OUTLET-B',
            'outlet_id' => $outletB->id,
            'outlet_name' => $outletB->name,
            'outlet_city' => $outletB->city,
            'outlet_district' => $outletB->district,
            'outlet_address_snapshot' => $outletB->address,
            'nama_pelanggan' => 'Budi',
            'no_hp' => '0822222222',
            'alamat' => 'Jakarta',
            'pesanan' => 'Burger',
            'jumlah' => 1,
            'harga_satuan' => 30000,
            'total_harga' => 30000,
            'jenis_belanja' => 'Delivery',
            'payment_method' => 'qris',
            'payment_status' => 'Menunggu Pembayaran',
            'status' => 'Menunggu Pembayaran',
        ]);

        $this->actingAs($kasir)
            ->get(route('kasir.pesanan'))
            ->assertOk()
            ->assertViewHas('orders', function ($orders) {
                return $orders->count() === 1
                    && $orders->first()->order_code === 'ORD-OUTLET-A';
            });
    }
}
