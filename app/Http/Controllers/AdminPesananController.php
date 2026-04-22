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
                'outlet_name' => $first->outlet_name,
                'outlet_city' => $first->outlet_city,
                'outlet_district' => $first->outlet_district,
                'outlet_address' => $first->outlet_address_snapshot,
                'outlet_label' => $this->buildOutletLabel($first->outlet_name, $first->outlet_district, $first->outlet_city),
                'jenis_belanja' => $first->jenis_belanja,
                'items_summary' => $itemsList,
                'total_harga' => $items->sum('total_harga'),
                'payment_method' => $first->payment_method ?? 'manual',
                'payment_method_label' => $this->paymentMethodLabel($first->payment_method),
                'payment_status' => $first->payment_status ?? 'Lunas',
                'paid_at' => $first->paid_at,
                'status' => $first->status,
                'created_at' => $first->created_at,
                'payment_proof' => $first->payment_proof,
                'payment_proof_url' => $first->payment_proof
                    ? asset('storage/' . $first->payment_proof)
                    : null,
                'payment_proof_uploaded_at' => $first->payment_proof_uploaded_at,
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

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'whatsapp_transfer' => 'Transfer via WhatsApp',
            'manual', null, '' => 'Manual',
            default => ucwords(str_replace('_', ' ', $paymentMethod)),
        };
    }

    private function buildOutletLabel(?string $name, ?string $district, ?string $city): string
    {
        $area = collect([$district, $city])
            ->filter()
            ->implode(', ');

        return trim(($name ?? 'Outlet') . ($area ? ' - ' . $area : ''));
    }
}
