<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminLaporanController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $pesananSukses = Pesanan::query()
            ->where('status', 'Selesai')
            ->orderBy('created_at', 'desc')
            ->get();

        // Total Pemasukan All-time (Hanya dari pesanan 'Selesai')
        $totalPemasukan = $pesananSukses->sum('total_harga');

        // Pemasukan Bulan Ini
        $pemasukanBulanIni = $pesananSukses
            ->filter(fn (Pesanan $pesanan) => $pesanan->created_at->month === $now->month
                && $pesanan->created_at->year === $now->year)
            ->sum('total_harga');

        // Pemasukan berdasarkan jenis belanja yang dipakai aplikasi saat ini
        $pemasukanAmbilDiOutlet = $pesananSukses
            ->filter(fn (Pesanan $pesanan) => $this->normalizeOrderType($pesanan->jenis_belanja) === 'take-away')
            ->sum('total_harga');

        $pemasukanDelivery = $pesananSukses
            ->filter(fn (Pesanan $pesanan) => $this->normalizeOrderType($pesanan->jenis_belanja) === 'delivery')
            ->sum('total_harga');

        $laporanPerOutlet = $pesananSukses
            ->groupBy(fn (Pesanan $pesanan) => $pesanan->outlet_id ?: ('manual-' . ($pesanan->outlet_name ?: 'tanpa-outlet')))
            ->map(function (Collection $orders) use ($now) {
                /** @var Pesanan $firstOrder */
                $firstOrder = $orders->first();
                $outletName = $firstOrder->outlet_name ?: 'Tanpa Outlet';
                $outletLabel = $this->buildOutletLabel(
                    $firstOrder->outlet_name,
                    $firstOrder->outlet_district,
                    $firstOrder->outlet_city
                );

                return (object) [
                    'outlet_id' => $firstOrder->outlet_id,
                    'outlet_name' => $outletName,
                    'outlet_label' => $outletLabel,
                    'total_pemasukan' => $orders->sum('total_harga'),
                    'pemasukan_bulan_ini' => $orders
                        ->filter(fn (Pesanan $pesanan) => $pesanan->created_at->month === $now->month
                            && $pesanan->created_at->year === $now->year)
                        ->sum('total_harga'),
                    'pemasukan_ambil_di_outlet' => $orders
                        ->filter(fn (Pesanan $pesanan) => $this->normalizeOrderType($pesanan->jenis_belanja) === 'take-away')
                        ->sum('total_harga'),
                    'pemasukan_delivery' => $orders
                        ->filter(fn (Pesanan $pesanan) => $this->normalizeOrderType($pesanan->jenis_belanja) === 'delivery')
                        ->sum('total_harga'),
                    'jumlah_transaksi' => $orders
                        ->map(fn (Pesanan $pesanan) => $pesanan->order_code ?: 'row-' . $pesanan->id)
                        ->unique()
                        ->count(),
                    'pesanan_terakhir' => $orders->sortByDesc('created_at')->first()?->created_at,
                ];
            })
            ->sortByDesc('total_pemasukan')
            ->values();

        $hasMultipleOutlets = $laporanPerOutlet->count() > 1;
        $pesananSukses = $pesananSukses->take(20)->values();

        return view('admin.laporan', compact(
            'totalPemasukan', 
            'pemasukanBulanIni', 
            'pemasukanAmbilDiOutlet', 
            'pemasukanDelivery',
            'laporanPerOutlet',
            'hasMultipleOutlets',
            'pesananSukses'
        ));
    }

    private function normalizeOrderType(?string $orderType): string
    {
        $normalized = strtolower(trim((string) $orderType));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return match ($normalized) {
            'take away', 'take-away', 'takeaway', 'ambil di outlet' => 'take-away',
            'delivery', 'diantar' => 'delivery',
            'dine in', 'makan ditempat', 'makan di tempat' => 'dine-in',
            default => $normalized,
        };
    }

    private function buildOutletLabel(?string $name, ?string $district, ?string $city): string
    {
        $area = collect([$district, $city])->filter()->implode(', ');

        if (! $name && ! $area) {
            return 'Tanpa Outlet';
        }

        return trim(($name ?: 'Tanpa Outlet') . ($area ? ' - ' . $area : ''));
    }
}
