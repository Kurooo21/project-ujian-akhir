<?php

namespace Tests\Feature;

use App\Models\Pesanan;
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
    }
}
