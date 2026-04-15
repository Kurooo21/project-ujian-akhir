<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jenis_belanja' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.pesanan_item' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric',
        ]);

        $adminWhatsApp = $this->normalizeWhatsAppNumber(
            (string) Setting::where('key', 'admin_whatsapp_number')->value('value')
        );

        if (! $adminWhatsApp) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp admin belum diatur. Silakan atur dulu di menu pengaturan admin.',
            ], 422);
        }

        $items = $request->items;
        $grandTotal = 0;
        $orderCode = null;

        DB::transaction(function () use ($request, $items, &$grandTotal, &$orderCode) {
            $firstItem = array_shift($items);
            $firstTotal = $firstItem['jumlah'] * $firstItem['harga_satuan'];

            $firstOrder = Pesanan::create([
                'user_id' => Auth::id(),
                'nama_pelanggan' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'pesanan' => $firstItem['pesanan_item'],
                'jumlah' => $firstItem['jumlah'],
                'harga_satuan' => $firstItem['harga_satuan'],
                'total_harga' => $firstTotal,
                'jenis_belanja' => $request->jenis_belanja,
                'payment_method' => 'whatsapp_transfer',
                'payment_status' => 'Menunggu Pembayaran',
                'status' => 'Menunggu Pembayaran',
            ]);

            $orderCode = 'ORD-' . str_pad((string) $firstOrder->id, 6, '0', STR_PAD_LEFT);
            $firstOrder->update(['order_code' => $orderCode]);
            $grandTotal += $firstTotal;

            foreach ($items as $item) {
                $itemTotal = $item['jumlah'] * $item['harga_satuan'];
                $grandTotal += $itemTotal;

                Pesanan::create([
                    'user_id' => Auth::id(),
                    'order_code' => $orderCode,
                    'nama_pelanggan' => $request->nama,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'pesanan' => $item['pesanan_item'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'total_harga' => $itemTotal,
                    'jenis_belanja' => $request->jenis_belanja,
                    'payment_method' => 'whatsapp_transfer',
                    'payment_status' => 'Menunggu Pembayaran',
                    'status' => 'Menunggu Pembayaran',
                ]);
            }
        });

        $whatsAppMessage = $this->buildWhatsAppMessage(
            $orderCode,
            $request->nama,
            $request->no_hp,
            $request->jenis_belanja,
            $request->alamat,
            $request->items,
            $grandTotal
        );

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil disimpan. Lanjutkan pembayaran di luar website dan kirim bukti ke admin.',
            'order_code' => $orderCode,
            'admin_whatsapp' => $adminWhatsApp,
            'whatsapp_url' => 'https://wa.me/' . $adminWhatsApp . '?text=' . rawurlencode($whatsAppMessage),
        ]);
    }

    public function index()
    {
        $pesanan = Pesanan::with('user')->orderBy('created_at', 'desc')->get();

        $grouped = $pesanan->groupBy(fn ($item) => $this->buildGroupId($item));

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (' . $item->jumlah . 'x)';
            })->implode(', ');
            $totalHarga = $items->sum('total_harga');
            $groupId = $this->buildGroupId($first);

            return [
                'id' => $first->id,
                'group_id' => $groupId,
                'order_code' => $first->order_code,
                'date' => $first->created_at->format('d/m/Y H:i'),
                'customerName' => $first->nama_pelanggan,
                'no_hp' => $first->no_hp,
                'alamat' => $first->alamat,
                'items' => $itemsList,
                'total' => $totalHarga,
                'jenis' => $first->jenis_belanja,
                'payment_method' => $first->payment_method ?? 'manual',
                'payment_status' => $first->payment_status ?? 'Lunas',
                'status' => $first->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // UPDATE STATUS PESANAN (Admin)
    public function updateStatus(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string',
            'status' => 'required|string'
        ]);

        $ordersQuery = $this->queryByGroupId($request->group_id);
        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $firstOrder = $orders->first();
        if (($firstOrder->payment_status ?? 'Lunas') !== 'Lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran belum dikonfirmasi. Konfirmasi pembayaran dulu sebelum mengubah status pesanan.',
            ], 422);
        }

        $ordersQuery->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    // GET PESANAN SAYA (User)
    public function userOrders()
    {
        $pesanan = Pesanan::where('user_id', Auth::id())
                          ->orderBy('created_at', 'desc')
                          ->get();

        $grouped = $pesanan->groupBy(fn ($item) => $this->buildGroupId($item));

        $orders = $grouped->map(function ($items) {
            $first = $items->first();
            $itemsList = $items->map(function ($item) {
                return $item->pesanan . ' (' . $item->jumlah . 'x)';
            })->implode(', ');
            $totalHarga = $items->sum('total_harga');

            return [
                'id' => $first->id,
                'group_id' => $this->buildGroupId($first),
                'order_code' => $first->order_code,
                'date' => $first->created_at->format('d/m/Y H:i'),
                'items' => $itemsList,
                'total' => $totalHarga,
                'jenis' => $first->jenis_belanja,
                'payment_status' => $first->payment_status ?? 'Lunas',
                'status' => $first->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
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

    private function normalizeWhatsAppNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (! $digits) {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (! str_starts_with($digits, '62')) {
            return '62' . $digits;
        }

        return $digits;
    }

    private function buildWhatsAppMessage(
        string $orderCode,
        string $customerName,
        string $phoneNumber,
        string $shoppingType,
        string $address,
        array $items,
        float|int $grandTotal
    ): string {
        $itemLines = collect($items)->map(function ($item) {
            return '- ' . $item['pesanan_item'] . ' x' . $item['jumlah'];
        })->implode("\n");

        $shoppingLabel = ucwords(str_replace('-', ' ', $shoppingType));

        return "Halo Admin, saya mau bayar pesanan {$orderCode} sebesar Rp" . number_format($grandTotal, 0, ',', '.') . ".\n"
            . "Nama: {$customerName}\n"
            . "No. WA: {$phoneNumber}\n"
            . "Jenis Belanja: {$shoppingLabel}\n"
            . "Alamat: {$address}\n"
            . "Detail Pesanan:\n{$itemLines}\n"
            . "Ini bukti transfernya ya.";
    }
}
