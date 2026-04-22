<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin.
     *
     * Halaman ini menampilkan:
     * - Total pendapatan hari ini
     * - Jumlah transaksi hari ini
     * - Produk terlaris (all-time)
     * - Jumlah menu dengan stok tipis berdasarkan resep
     * - 10 transaksi terkini
     */
    public function index()
    {
        $dashboardTimezone = config('app.dashboard_timezone', env('APP_DASHBOARD_TIMEZONE', 'Asia/Jakarta'));
        $today = Carbon::now($dashboardTimezone);
        $allOrders = Pesanan::orderBy('created_at', 'desc')->get();
        $products = Product::with('recipeItems.ingredient')
            ->orderBy('name')
            ->get();

        [$pesananRingkasan, $summaryDate, $isTodaySummary] = $this->resolveSummaryOrders($today, $allOrders);

        $ringkasanPesanan = $this->mapGroupedOrders(
            $pesananRingkasan->groupBy(fn ($item) => $this->buildGroupId($item))
        );

        $transaksiValid = $ringkasanPesanan
            ->reject(fn ($order) => $this->isCancelledStatus($order->status));

        $transaksiLunas = $transaksiValid
            ->filter(fn ($order) => $this->isPaid($order->payment_status));

        $totalPendapatan = $transaksiLunas->sum('total_harga');
        $jumlahTransaksi = $transaksiValid->count();

        $produkTerlaris = Pesanan::query()
            ->where(function ($query) {
                $query->where('payment_status', 'Lunas')
                    ->orWhereNull('payment_status');
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Dibatalkan');
            })
            ->select('pesanan', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('pesanan')
            ->orderByDesc('total_terjual')
            ->first();

        $productsWithRecipe = $products->filter(fn (Product $product) => $product->has_recipe);
        $stokTipis = $productsWithRecipe->isNotEmpty()
            ? $productsWithRecipe->filter(fn (Product $product) => $product->is_low_stock)->count()
            : null;
        $stokTipisDescription = $productsWithRecipe->isNotEmpty()
            ? 'Jumlah menu dengan porsi tersedia di bawah batas minimum.'
            : 'Tambahkan bahan baku dan resep per porsi agar kartu ini aktif.';

        $transaksiTerkini = $this->mapGroupedOrders(
            $allOrders->groupBy(fn ($item) => $this->buildGroupId($item))
        )
            ->take(10)
            ->values();

        $hasSummaryData = $ringkasanPesanan->isNotEmpty();
        $summaryBadge = $hasSummaryData
            ? ($isTodaySummary ? 'Hari Ini' : 'Data Terakhir')
            : 'Belum Ada Data';
        $summaryDateLabel = $hasSummaryData
            ? $summaryDate->locale('id')->translatedFormat('d F Y')
            : 'Belum ada transaksi masuk';
        $summaryDescription = $hasSummaryData
            ? ($isTodaySummary ? 'Ringkasan transaksi hari ini' : 'Ringkasan pada tanggal transaksi terakhir')
            : 'Dashboard akan otomatis terisi saat sudah ada pesanan.';

        return view('admin.dashboard', compact(
            'totalPendapatan',
            'jumlahTransaksi',
            'produkTerlaris',
            'stokTipis',
            'stokTipisDescription',
            'transaksiTerkini',
            'summaryBadge',
            'summaryDateLabel',
            'summaryDescription',
            'hasSummaryData'
        ));
    }

    private function buildGroupId(Pesanan $pesanan): string
    {
        if ($pesanan->order_code) {
            return $pesanan->order_code;
        }

        return $pesanan->user_id . '|' . $pesanan->created_at->format('Y-m-d H:i:s');
    }

    private function resolveSummaryOrders(Carbon $today, Collection $allOrders): array
    {
        $pesananRingkasan = $this->filterOrdersByLocalDate($allOrders, $today);

        if ($pesananRingkasan->isNotEmpty()) {
            return [$pesananRingkasan, $today, true];
        }

        $latestOrder = $allOrders->first();

        if (! $latestOrder) {
            return [collect(), $today, true];
        }

        $summaryDate = $latestOrder->created_at->copy()->timezone($today->getTimezone());

        return [
            $this->filterOrdersByLocalDate($allOrders, $summaryDate),
            $summaryDate,
            false,
        ];
    }

    private function filterOrdersByLocalDate(Collection $orders, Carbon $date): Collection
    {
        return $orders
            ->filter(fn ($order) => $order->created_at->copy()->timezone($date->getTimezone())->isSameDay($date))
            ->values();
    }

    private function mapGroupedOrders(Collection $groupedOrders): Collection
    {
        return $groupedOrders->map(function ($items) {
            $first = $items->first();

            return (object) [
                'id' => $first->id,
                'order_code' => $first->order_code,
                'nama_pelanggan' => $first->nama_pelanggan,
                'no_hp' => $first->no_hp,
                'pesanan' => $items->map(fn ($item) => $item->pesanan . ' (x' . $item->jumlah . ')')->implode(', '),
                'total_harga' => $items->sum('total_harga'),
                'jenis_belanja' => $first->jenis_belanja,
                'payment_status' => $first->payment_status ?? 'Lunas',
                'status' => $first->status,
                'created_at' => $first->created_at,
            ];
        });
    }

    private function isPaid(?string $paymentStatus): bool
    {
        if ($paymentStatus === null) {
            return true;
        }

        return strcasecmp(trim($paymentStatus), 'Lunas') === 0;
    }

    private function isCancelledStatus(?string $status): bool
    {
        if ($status === null) {
            return false;
        }

        return strcasecmp(trim($status), 'Dibatalkan') === 0;
    }
}
