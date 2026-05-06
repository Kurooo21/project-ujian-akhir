<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_membedakan_ringkasan_jika_transaksi_berasal_dari_lebih_dari_satu_outlet(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'laporan-admin@example.com',
            'username' => 'laporan-admin',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

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

        $now = Carbon::now();

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-ANT-001',
            'outlet_id' => $outletA->id,
            'outlet_name' => $outletA->name,
            'outlet_city' => $outletA->city,
            'outlet_district' => $outletA->district,
            'outlet_address_snapshot' => $outletA->address,
            'nama_pelanggan' => 'Andi',
            'no_hp' => '0811111111',
            'alamat' => 'Bandung',
            'pesanan' => 'Ayam Crispy',
            'jumlah' => 2,
            'harga_satuan' => 15000,
            'total_harga' => 30000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'qris',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-ANT-002',
            'outlet_id' => $outletA->id,
            'outlet_name' => $outletA->name,
            'outlet_city' => $outletA->city,
            'outlet_district' => $outletA->district,
            'outlet_address_snapshot' => $outletA->address,
            'nama_pelanggan' => 'Budi',
            'no_hp' => '0822222222',
            'alamat' => 'Bandung',
            'pesanan' => 'Es Teh',
            'jumlah' => 2,
            'harga_satuan' => 15000,
            'total_harga' => 30000,
            'jenis_belanja' => 'Delivery',
            'payment_method' => 'qris',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-TEB-001',
            'outlet_id' => $outletB->id,
            'outlet_name' => $outletB->name,
            'outlet_city' => $outletB->city,
            'outlet_district' => $outletB->district,
            'outlet_address_snapshot' => $outletB->address,
            'nama_pelanggan' => 'Citra',
            'no_hp' => '0833333333',
            'alamat' => 'Jakarta',
            'pesanan' => 'Burger',
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'total_harga' => 25000,
            'jenis_belanja' => 'Delivery',
            'payment_method' => 'qris',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Pesanan::create([
            'user_id' => $admin->id,
            'order_code' => 'ORD-CANCELLED',
            'outlet_id' => $outletB->id,
            'outlet_name' => $outletB->name,
            'outlet_city' => $outletB->city,
            'outlet_district' => $outletB->district,
            'outlet_address_snapshot' => $outletB->address,
            'nama_pelanggan' => 'Dina',
            'no_hp' => '0844444444',
            'alamat' => 'Jakarta',
            'pesanan' => 'Milkshake',
            'jumlah' => 1,
            'harga_satuan' => 15000,
            'total_harga' => 15000,
            'jenis_belanja' => 'Take Away',
            'payment_method' => 'qris',
            'payment_status' => 'Lunas',
            'status' => 'Dibatalkan',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan'));

        $response->assertOk();
        $response->assertViewHas('hasMultipleOutlets', true);
        $response->assertViewHas('totalPemasukan', 85000);
        $response->assertViewHas('pemasukanAmbilDiOutlet', 30000);
        $response->assertViewHas('pemasukanDelivery', 55000);
        $response->assertViewHas('laporanPerOutlet', function ($laporanPerOutlet) {
            $laporanByName = $laporanPerOutlet->keyBy('outlet_name');

            return $laporanByName->count() === 2
                && (int) $laporanByName['Chi-Pok Antapani']->total_pemasukan === 60000
                && (int) $laporanByName['Chi-Pok Antapani']->jumlah_transaksi === 2
                && (int) $laporanByName['Chi-Pok Tebet']->total_pemasukan === 25000
                && (int) $laporanByName['Chi-Pok Tebet']->jumlah_transaksi === 1;
        });
        $response->assertSee('Chi-Pok Antapani');
        $response->assertSee('Chi-Pok Tebet');
    }
}
