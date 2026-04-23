<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithGroupedOrders;
use App\Models\Pesanan;

class AdminPesananController extends Controller
{
    use InteractsWithGroupedOrders;

    public function index()
    {
        $groupedOrders = $this->summarizeGroupedOrders(
            Pesanan::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(fn ($item) => $this->buildGroupId($item))
        );

        $pendingPaymentCount = $groupedOrders
            ->filter(fn ($order) => ! $this->isPaid($order->payment_status))
            ->count();

        $activeOrderCount = $groupedOrders
            ->filter(fn ($order) => in_array($order->status, ['Diproses', 'Pesanan Siap', 'Sedang Diantar'], true))
            ->count();

        $completedOrderCount = $groupedOrders
            ->where('status', 'Selesai')
            ->count();

        return view('admin.pesanan', [
            'orders' => $groupedOrders,
            'pendingPaymentCount' => $pendingPaymentCount,
            'activeOrderCount' => $activeOrderCount,
            'completedOrderCount' => $completedOrderCount,
        ]);
    }

    private function isPaid(?string $paymentStatus): bool
    {
        if ($paymentStatus === null) {
            return true;
        }

        return strcasecmp(trim($paymentStatus), 'Lunas') === 0;
    }
}
