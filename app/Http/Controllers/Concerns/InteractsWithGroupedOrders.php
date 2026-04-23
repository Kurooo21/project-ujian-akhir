<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait InteractsWithGroupedOrders
{
    protected function buildGroupId(Pesanan $pesanan): string
    {
        if ($pesanan->order_code) {
            return $pesanan->order_code;
        }

        return $pesanan->user_id . '|' . $pesanan->created_at->format('Y-m-d H:i:s');
    }

    protected function queryByGroupId(string $groupId): Builder
    {
        if (str_starts_with($groupId, 'ORD-')) {
            return Pesanan::where('order_code', $groupId);
        }

        [$userId, $createdAt] = $this->parseLegacyGroupId($groupId);

        return Pesanan::where('user_id', $userId)
            ->where('created_at', $createdAt);
    }

    protected function parseLegacyGroupId(string $groupId): array
    {
        if (str_contains($groupId, '|')) {
            return explode('|', $groupId, 2);
        }

        if (str_contains($groupId, '_')) {
            return explode('_', $groupId, 2);
        }

        abort(422, 'Format group_id tidak valid.');
    }

    protected function paymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'whatsapp_transfer' => 'Transfer via WhatsApp',
            'manual', null, '' => 'Manual',
            default => ucwords(str_replace('_', ' ', $paymentMethod)),
        };
    }

    protected function buildOutletLabel(?string $name, ?string $district, ?string $city): string
    {
        $area = collect([$district, $city])
            ->filter()
            ->implode(', ');

        return trim(($name ?? 'Outlet') . ($area ? ' - ' . $area : ''));
    }

    protected function summarizeGroupedOrders(Collection $groupedOrders): Collection
    {
        return $groupedOrders
            ->map(function ($items) {
                $first = $items->first();

                return (object) [
                    'id' => $first->id,
                    'group_id' => $this->buildGroupId($first),
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
                    'items_summary' => $items->map(fn ($item) => $item->pesanan . ' (x' . $item->jumlah . ')')->implode(', '),
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
            })
            ->values();
    }
}
