<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;

class AdminPesananController extends Controller
{
    public function index()
    {
        $pesanan = Pesanan::with('user')->orderBy('created_at', 'desc')->get();
        $grouped = $pesanan->groupBy(fn ($item) => $this->buildGroupId($item));

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (x' . $item->jumlah . ')';
            })->implode(', ');

            $groupId = $this->buildGroupId($first);

            return (object) [
                'group_id' => $groupId,
                'order_code' => $first->order_code,
                'nama_pelanggan' => $first->nama_pelanggan,
                'no_hp' => $first->no_hp,
                'alamat' => $first->alamat,
                'jenis_belanja' => $first->jenis_belanja,
                'items_summary' => $itemsList,
                'total_harga' => $items->sum('total_harga'),
                'payment_method' => $first->payment_method ?? 'manual',
                'payment_status' => $first->payment_status ?? 'Lunas',
                'paid_at' => $first->paid_at,
                'status' => $first->status,
                'created_at' => $first->created_at,
            ];
        })->values();

        return view('admin.pesanan', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $ordersQuery = $this->queryByGroupId($request->group_id);
        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return redirect()->route('admin.pesanan');
        }

        $firstOrder = $orders->first();
        if (($firstOrder->payment_status ?? 'Lunas') !== 'Lunas') {
            return redirect()->route('admin.pesanan');
        }

        $ordersQuery->update(['status' => $request->status]);

        return redirect()->route('admin.pesanan');
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string',
        ]);

        $ordersQuery = $this->queryByGroupId($request->group_id);
        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return redirect()->route('admin.pesanan');
        }

        $ordersQuery->update([
            'payment_status' => 'Lunas',
            'paid_at' => now(),
            'status' => 'Diproses',
        ]);

        return redirect()->route('admin.pesanan');
    }

    private function buildGroupId(Pesanan $pesanan): string
    {
        if ($pesanan->order_code) {
            return $pesanan->order_code;
        }

        return $pesanan->user_id . '|' . $pesanan->created_at->format('Y-m-d H:i:s');
    }

    private function queryByGroupId(string $groupId)
    {
        if (str_starts_with($groupId, 'ORD-')) {
            return Pesanan::where('order_code', $groupId);
        }

        [$userId, $createdAt] = $this->parseLegacyGroupId($groupId);

        return Pesanan::where('user_id', $userId)
            ->where('created_at', $createdAt);
    }

    private function parseLegacyGroupId(string $groupId): array
    {
        if (str_contains($groupId, '|')) {
            return explode('|', $groupId, 2);
        }

        if (str_contains($groupId, '_')) {
            return explode('_', $groupId, 2);
        }

        abort(422, 'Format group_id tidak valid.');
    }
}
