<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createUserAndOutlet(): array
    {
        $user = User::create([
            'name' => 'Man',
            'email' => 'man@example.com',
            'username' => 'man',
            'password' => bcrypt('secret'),
            'role' => 'pelanggan',
        ]);

        $outlet = Outlet::create([
            'name' => 'Chi-Pok',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Antapani',
            'address' => 'Jl. Antapani No. 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$user, $outlet];
    }

    public function test_checkout_with_same_client_request_id_only_creates_one_order(): void
    {
        [$user, $outlet] = $this->createUserAndOutlet();

        $payload = [
            'nama' => 'man',
            'no_hp' => '082345632',
            'alamat' => 'Antapani, Bandung',
            'jenis_belanja' => 'Delivery',
            'outlet_id' => $outlet->id,
            'payment_method' => 'qris',
            'client_request_id' => 'checkout-req-001',
            'items' => [
                [
                    'pesanan_item' => 'KEJU POKPOK',
                    'jumlah' => 1,
                    'harga_satuan' => 25000,
                ],
            ],
        ];

        $firstResponse = $this->actingAs($user)->postJson(route('pesanan.store'), $payload);
        $firstResponse->assertOk()->assertJson([
            'success' => true,
        ]);

        $secondResponse = $this->actingAs($user)->postJson(route('pesanan.store'), $payload);
        $secondResponse->assertOk()->assertJson([
            'success' => true,
            'order_code' => $firstResponse->json('order_code'),
        ]);

        $this->assertDatabaseCount('pesanan', 1);
        $this->assertSame($firstResponse->json('order_code'), Pesanan::query()->value('order_code'));
    }

    public function test_take_away_checkout_does_not_require_address(): void
    {
        [$user, $outlet] = $this->createUserAndOutlet();

        $response = $this->actingAs($user)->postJson(route('pesanan.store'), [
            'nama' => 'man',
            'no_hp' => '082345632',
            'alamat' => '',
            'jenis_belanja' => 'Take Away',
            'outlet_id' => $outlet->id,
            'payment_method' => 'qris',
            'client_request_id' => 'checkout-req-take-away',
            'items' => [
                [
                    'pesanan_item' => 'KEJU POKPOK',
                    'jumlah' => 1,
                    'harga_satuan' => 25000,
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pesanan', [
            'jenis_belanja' => 'Take Away',
            'alamat' => 'Ambil di outlet',
        ]);
    }

    public function test_dine_in_checkout_is_rejected_after_option_is_removed(): void
    {
        [$user, $outlet] = $this->createUserAndOutlet();

        $response = $this->actingAs($user)->postJson(route('pesanan.store'), [
            'nama' => 'man',
            'no_hp' => '082345632',
            'alamat' => '',
            'jenis_belanja' => 'Dine In',
            'outlet_id' => $outlet->id,
            'payment_method' => 'qris',
            'client_request_id' => 'checkout-req-dine-in',
            'items' => [
                [
                    'pesanan_item' => 'KEJU POKPOK',
                    'jumlah' => 1,
                    'harga_satuan' => 25000,
                ],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'jenis_belanja',
        ]);
    }
}
