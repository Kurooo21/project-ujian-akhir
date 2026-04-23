<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithGroupedOrders;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KasirDashboardController extends Controller
{
    use InteractsWithGroupedOrders;

    public function index()
    {
        $kasir = Auth::user()->loadMissing('outlet');
        $assignedOutlet = $kasir->outlet;
        $timezone = config('app.dashboard_timezone', env('APP_DASHBOARD_TIMEZONE', 'Asia/Jakarta'));
        $today = Carbon::now($timezone);

        $orders = $assignedOutlet
            ? $this->summarizeGroupedOrders(
                Pesanan::query()
                    ->where('outlet_id', $assignedOutlet->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy(fn ($item) => $this->buildGroupId($item))
            )
            : collect();

        $todayOrders = $this->filterOrdersByLocalDate($orders, $today);
        $validTodayOrders = $todayOrders->reject(fn ($order) => $this->isCancelledStatus($order->status));

        $todayRevenue = $validTodayOrders
            ->filter(fn ($order) => $this->isPaid($order->payment_status))
            ->sum('total_harga');

        $pendingPaymentCount = $orders
            ->reject(fn ($order) => $this->isCancelledStatus($order->status))
            ->filter(fn ($order) => ! $this->isPaid($order->payment_status))
            ->count();

        $activeOrderCount = $orders
            ->filter(fn ($order) => in_array($order->status, ['Diproses', 'Pesanan Siap', 'Sedang Diantar'], true))
            ->count();

        $completedTodayCount = $todayOrders
            ->where('status', 'Selesai')
            ->count();

        return view('kasir.dashboard', [
            'assignedOutlet' => $assignedOutlet,
            'todayRevenue' => $todayRevenue,
            'todayOrderCount' => $validTodayOrders->count(),
            'pendingPaymentCount' => $pendingPaymentCount,
            'activeOrderCount' => $activeOrderCount,
            'completedTodayCount' => $completedTodayCount,
            'recentOrders' => $orders->take(8),
        ]);
    }

    private function filterOrdersByLocalDate(Collection $orders, Carbon $date): Collection
    {
        return $orders
            ->filter(fn ($order) => $order->created_at->copy()->timezone($date->getTimezone())->isSameDay($date))
            ->values();
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
