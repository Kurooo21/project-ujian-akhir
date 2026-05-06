<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithGroupedOrders;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KasirDashboardController extends Controller
{
    // Menggunakan trait untuk mengelompokkan pesanan berdasarkan order_code
    use InteractsWithGroupedOrders;

    public function index()
    {
        // 1. Ambil data kasir yang sedang login, beserta relasi 'outlet'-nya
        $kasir = Auth::user()->loadMissing('outlet');
        $assignedOutlet = $kasir->outlet;
        
        // 2. Tentukan zona waktu untuk perhitungan data hari ini
        $timezone = config('app.dashboard_timezone', env('APP_DASHBOARD_TIMEZONE', 'Asia/Jakarta'));
        $today = Carbon::now($timezone);

        // 3. Ambil dan kelompokkan pesanan milik outlet kasir tersebut
        $orders = $assignedOutlet
            ? $this->summarizeGroupedOrders(
                Pesanan::query()
                    ->where('outlet_id', $assignedOutlet->id) // Hanya pesanan dari outlet ini
                    ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
                    ->get()
                    ->groupBy(fn ($item) => $this->buildGroupId($item)) // Kelompokkan berdasarkan order_code
            )
            : collect(); // Jika kasir tidak punya outlet, kembalikan koleksi kosong

        // 4. Filter pesanan yang dibuat khusus hari ini
        $todayOrders = $this->filterOrdersByLocalDate($orders, $today);
        
        // 5. Singkirkan pesanan yang statusnya 'Dibatalkan' untuk perhitungan hari ini
        $validTodayOrders = $todayOrders->reject(fn ($order) => $this->isCancelledStatus($order->status));

        // 6. Hitung total pendapatan hari ini (hanya dari pesanan yang sudah lunas)
        $todayRevenue = $validTodayOrders
            ->filter(fn ($order) => $this->isPaid($order->payment_status))
            ->sum('total_harga');

        // 7. Hitung jumlah pesanan yang belum lunas (menunggu pembayaran)
        $pendingPaymentCount = $orders
            ->reject(fn ($order) => $this->isCancelledStatus($order->status))
            ->filter(fn ($order) => ! $this->isPaid($order->payment_status))
            ->count();

        // 8. Hitung jumlah pesanan yang sedang aktif (Diproses, Pesanan Siap, Sedang Diantar)
        $activeOrderCount = $orders
            ->filter(fn ($order) => in_array($order->status, ['Diproses', 'Pesanan Siap', 'Sedang Diantar'], true))
            ->count();

        // 9. Hitung jumlah pesanan hari ini yang sudah berstatus 'Selesai'
        $completedTodayCount = $todayOrders
            ->where('status', 'Selesai')
            ->count();

        // 10. Kirim semua data perhitungan ke halaman view (kasir/dashboard.blade.php)
        return view('kasir.dashboard', [
            'assignedOutlet' => $assignedOutlet,
            'todayRevenue' => $todayRevenue,
            'todayOrderCount' => $validTodayOrders->count(),
            'pendingPaymentCount' => $pendingPaymentCount,
            'activeOrderCount' => $activeOrderCount,
            'completedTodayCount' => $completedTodayCount,
            'recentOrders' => $orders->take(8), // Ambil 8 pesanan terakhir untuk tabel pesanan terbaru
        ]);
    }

    /**
     * Fungsi bantuan untuk menyaring pesanan berdasarkan tanggal hari ini sesuai zona waktu.
     */
    private function filterOrdersByLocalDate(Collection $orders, Carbon $date): Collection
    {
        return $orders
            ->filter(fn ($order) => $order->created_at->copy()->timezone($date->getTimezone())->isSameDay($date))
            ->values();
    }

    /**
     * Fungsi bantuan untuk mengecek apakah pesanan sudah lunas.
     */
    private function isPaid(?string $paymentStatus): bool
    {
        if ($paymentStatus === null) {
            return true; // Asumsi lawas jika sistem pembayaran null
        }

        return strcasecmp(trim($paymentStatus), 'Lunas') === 0;
    }

    /**
     * Fungsi bantuan untuk mengecek apakah pesanan dibatalkan.
     */
    private function isCancelledStatus(?string $status): bool
    {
        if ($status === null) {
            return false;
        }

        return strcasecmp(trim($status), 'Dibatalkan') === 0;
    }
}
