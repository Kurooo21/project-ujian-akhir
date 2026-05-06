<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithGroupedOrders;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KasirPesananController extends Controller
{
    // Menggunakan trait untuk mempermudah pengelompokan pesanan yang punya kode/grup sama
    use InteractsWithGroupedOrders;

    /**
     * Menampilkan daftar pesanan untuk kasir
     */
    public function index()
    {
        // 1. Ambil data kasir yang login dan outlet tempat dia bekerja
        $kasir = Auth::user()->loadMissing('outlet');
        $assignedOutlet = $kasir->outlet;

        // 2. Ambil pesanan khusus untuk outlet kasir tersebut dan kelompokkan berdasarkan order_code
        $orders = $assignedOutlet
            ? $this->summarizeGroupedOrders(
                Pesanan::with('user') // Ambil juga data user pemesannya
                    ->where('outlet_id', $assignedOutlet->id)
                    ->orderBy('created_at', 'desc') // Urutkan dari pesanan terbaru
                    ->get()
                    ->groupBy(fn ($item) => $this->buildGroupId($item))
            )
            : collect();

        // 3. Tampilkan halaman daftar pesanan (kasir/pesanan.blade.php)
        return view('kasir.pesanan', compact('orders', 'assignedOutlet'));
    }

    /**
     * Mengubah status pesanan (misal: dari "Diproses" ke "Pesanan Siap")
     */
    public function updateStatus(Request $request): RedirectResponse
    {
        // 1. Cek apakah kasir punya outlet
        if (! Auth::user()->outlet_id) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Akun kasir ini belum ditautkan ke outlet. Hubungi admin untuk mengatur outlet kerja.');
        }

        // 2. Validasi input dari form
        $request->validate([
            'group_id' => 'required|string',
            'status' => [
                'required',
                'string',
                Rule::in(['Diproses', 'Pesanan Siap', 'Sedang Diantar', 'Selesai', 'Dibatalkan']),
            ],
        ]);

        // 3. Cari pesanan berdasarkan ID Grup (order_code) dan outlet
        $ordersQuery = $this->queryByGroupId($request->group_id)
            ->where('outlet_id', Auth::user()->outlet_id);

        $orders = $ordersQuery->get();

        // 4. Jika pesanan tidak ditemukan
        if ($orders->isEmpty()) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pesanan tidak ditemukan untuk outlet kasir ini.');
        }

        $firstOrder = $orders->first();

        // 5. Pastikan pesanan sudah lunas sebelum statusnya bisa diubah (selain dibatalkan)
        if (($firstOrder->payment_status ?? 'Lunas') !== 'Lunas') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pembayaran belum dikonfirmasi. Konfirmasi pembayaran dulu sebelum mengubah status pesanan.');
        }

        // 6. Update status pesanan di database
        $ordersQuery->update(['status' => $request->status]);

        return redirect()
            ->route('kasir.pesanan')
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Mengkonfirmasi pembayaran dari pelanggan (misal transfer bank/cash)
     */
    public function confirmPayment(Request $request): RedirectResponse
    {
        // 1. Cek apakah kasir punya outlet
        if (! Auth::user()->outlet_id) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Akun kasir ini belum ditautkan ke outlet. Hubungi admin untuk mengatur outlet kerja.');
        }

        // 2. Validasi input
        $request->validate([
            'group_id' => 'required|string',
        ]);

        // 3. Gunakan DB transaction agar jika ada error di tengah jalan, perubahan dibatalkan semua
        $result = DB::transaction(function () use ($request) {
            // Ambil pesanan dan kunci baris di database (lockForUpdate) agar tidak diubah proses lain
            /** @var \Illuminate\Database\Eloquent\Collection $orders */
            $orders = $this->queryByGroupId($request->group_id)
                ->where('outlet_id', Auth::user()->outlet_id)
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return ['status' => 'not_found'];
            }

            $firstOrder = $orders->first();

            // Cegah double konfirmasi jika sudah lunas
            if (($firstOrder->payment_status ?? 'Lunas') === 'Lunas') {
                return ['status' => 'already_paid'];
            }

            // Kumpulkan ID pesanan secara manual agar tidak ada warning di VS Code
            $orderIds = [];
            foreach ($orders as $order) {
                $orderIds[] = $order->id;
            }

            // Update status pesanan menjadi Lunas dan lanjut ke tahap 'Diproses'
            Pesanan::whereIn('id', $orderIds)
                ->update([
                    'payment_status' => 'Lunas',
                    'paid_at' => now(), // Catat waktu pembayaran
                    'status' => 'Diproses', // Otomatis masuk dapur
                ]);

            return ['status' => 'success'];
        });

        // 4. Tangani hasil dari transaksi database
        if ($result['status'] === 'not_found') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pesanan tidak ditemukan untuk outlet kasir ini.');
        }

        if ($result['status'] === 'already_paid') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('success', 'Pembayaran pesanan ini sudah pernah dikonfirmasi sebelumnya.');
        }

        return redirect()
            ->route('kasir.pesanan')
            ->with('success', 'Pembayaran berhasil dikonfirmasi dan pesanan masuk ke proses dapur.');
    }

    // Fungsi normalisasi nama produk untuk keperluan pencarian atau logika internal
    private function normalizeProductName(string $productName): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $productName)));
    }
}
