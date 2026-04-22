<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PesananController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'jenis_belanja' => 'required|string|max:50',
            'outlet_id' => 'required|integer|exists:outlets,id',
            'payment_method' => 'required|string|in:qris,bank_transfer',
            'client_request_id' => 'nullable|string|max:100',
            'alamat' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => $this->requiresDeliveryAddress((string) $request->input('jenis_belanja'))),
            ],
            'items' => 'required|array|min:1',
            'items.*.pesanan_item' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric',
        ]);

        $clientRequestId = trim((string) $request->input('client_request_id', ''));
        $userId = Auth::id();
        $responseCacheKey = $this->checkoutResponseCacheKey($userId, $clientRequestId);

        if ($clientRequestId !== '') {
            $cachedResponse = Cache::get($responseCacheKey);

            if (is_array($cachedResponse)) {
                return response()->json($cachedResponse);
            }
        }

        $checkoutLock = null;

        if ($clientRequestId !== '') {
            $checkoutLock = Cache::lock($this->checkoutLockKey($userId, $clientRequestId), 10);

            if (! $checkoutLock->get()) {
                $cachedResponse = Cache::get($responseCacheKey);

                if (is_array($cachedResponse)) {
                    return response()->json($cachedResponse);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan checkout yang sama sedang diproses. Mohon tunggu sebentar lalu coba lagi.',
                ], 409);
            }
        }

        try {
            $settings = $this->mergePaymentSettingsWithDemoDefaults(
                Setting::pluck('value', 'key')->all()
            );
            $outlet = Outlet::query()
                ->whereKey($request->integer('outlet_id'))
                ->where('is_active', true)
                ->first();

            if (! $outlet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet yang dipilih tidak tersedia atau sedang nonaktif.',
                ], 422);
            }

            $paymentMethod = (string) $request->payment_method;
            $resolvedAddress = $this->resolveOrderAddress(
                (string) $request->jenis_belanja,
                $request->input('alamat')
            );

            $adminWhatsApp = $this->normalizeWhatsAppNumber((string) ($settings['admin_whatsapp_number'] ?? ''));
            $items = $request->items;
            $grandTotal = 0;
            $orderCode = null;

            DB::transaction(function () use ($request, $items, $outlet, $paymentMethod, $resolvedAddress, &$grandTotal, &$orderCode) {
                $firstItem = array_shift($items);
                $firstTotal = $firstItem['jumlah'] * $firstItem['harga_satuan'];

                $firstOrder = Pesanan::create([
                    'user_id' => Auth::id(),
                    'outlet_id' => $outlet->id,
                    'outlet_name' => $outlet->name,
                    'outlet_city' => $outlet->city,
                    'outlet_district' => $outlet->district,
                    'outlet_address_snapshot' => $outlet->address,
                    'nama_pelanggan' => $request->nama,
                    'no_hp' => $request->no_hp,
                    'alamat' => $resolvedAddress,
                    'pesanan' => $firstItem['pesanan_item'],
                    'jumlah' => $firstItem['jumlah'],
                    'harga_satuan' => $firstItem['harga_satuan'],
                    'total_harga' => $firstTotal,
                    'jenis_belanja' => $request->jenis_belanja,
                    'payment_method' => $paymentMethod,
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
                        'outlet_id' => $outlet->id,
                        'outlet_name' => $outlet->name,
                        'outlet_city' => $outlet->city,
                        'outlet_district' => $outlet->district,
                        'outlet_address_snapshot' => $outlet->address,
                        'nama_pelanggan' => $request->nama,
                        'no_hp' => $request->no_hp,
                        'alamat' => $resolvedAddress,
                        'pesanan' => $item['pesanan_item'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['harga_satuan'],
                        'total_harga' => $itemTotal,
                        'jenis_belanja' => $request->jenis_belanja,
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'Menunggu Pembayaran',
                        'status' => 'Menunggu Pembayaran',
                    ]);
                }
            });

            $paymentDetails = $this->buildPaymentDetails($paymentMethod, $settings);
            $whatsAppMessage = $this->buildWhatsAppMessage(
                $orderCode,
                $request->nama,
                $request->no_hp,
                $request->jenis_belanja,
                $resolvedAddress,
                $request->items,
                $grandTotal,
                $paymentMethod,
                $outlet
            );

            $responsePayload = [
                'success' => true,
                'message' => 'Pesanan berhasil disimpan. Ini adalah simulasi pembayaran demo sesuai metode yang dipilih.',
                'order_code' => $orderCode,
                'outlet' => [
                    'id' => $outlet->id,
                    'name' => $outlet->name,
                    'city' => $outlet->city,
                    'district' => $outlet->district,
                    'address' => $outlet->address,
                    'label' => $this->buildOutletLabel($outlet->name, $outlet->district, $outlet->city),
                ],
                'payment_method' => $paymentMethod,
                'payment_method_label' => $this->paymentMethodLabel($paymentMethod),
                'payment_total' => $grandTotal,
                'payment_details' => $paymentDetails,
                'payment_instructions' => $this->buildPaymentInstructions($paymentMethod, $paymentDetails, $grandTotal, $orderCode),
                'admin_whatsapp' => $adminWhatsApp,
                'whatsapp_url' => $adminWhatsApp
                    ? 'https://wa.me/' . $adminWhatsApp . '?text=' . rawurlencode($whatsAppMessage)
                    : null,
            ];

            if ($clientRequestId !== '') {
                Cache::put($responseCacheKey, $responsePayload, now()->addMinutes(5));
            }

            return response()->json($responsePayload);
        } finally {
            optional($checkoutLock)->release();
        }
    }

    // UPLOAD BUKTI PEMBAYARAN
    public function uploadProof(Request $request, string $orderCode)
    {
        $request->validate([
            'payment_proof' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120', // max 5MB
        ], [
            'payment_proof.required' => 'Pilih file gambar bukti pembayaran terlebih dahulu.',
            'payment_proof.image'    => 'File harus berupa gambar.',
            'payment_proof.mimes'    => 'Format gambar harus JPG, PNG, atau WEBP.',
            'payment_proof.max'      => 'Ukuran gambar maksimal 5MB.',
        ]);

        // Cari pesanan berdasarkan order_code milik user yang login
        $orders = Pesanan::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $firstOrder = $orders->first();

        // Hapus file lama jika ada
        if ($firstOrder->payment_proof) {
            Storage::disk('public')->delete($firstOrder->payment_proof);
        }

        // Simpan file baru
        $path = $request->file('payment_proof')->store('payment_proofs', 'public');
        $uploadedAt = now();

        // Update semua baris dengan order_code yang sama
        Pesanan::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->update([
                'payment_proof' => $path,
                'payment_proof_uploaded_at' => $uploadedAt,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload. Tunggu konfirmasi dari admin ya!',
            'proof_url' => Storage::disk('public')->url($path),
            'uploaded_at' => $uploadedAt->format('d/m/Y H:i'),
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
                'outlet_name' => $first->outlet_name,
                'outlet_city' => $first->outlet_city,
                'outlet_district' => $first->outlet_district,
                'outlet_address' => $first->outlet_address_snapshot,
                'outlet_label' => $this->buildOutletLabel($first->outlet_name, $first->outlet_district, $first->outlet_city),
                'items' => $itemsList,
                'total' => $totalHarga,
                'jenis' => $first->jenis_belanja,
                'payment_method' => $first->payment_method ?? 'manual',
                'payment_method_label' => $this->paymentMethodLabel($first->payment_method),
                'payment_status' => $first->payment_status ?? 'Lunas',
                'payment_proof' => $first->payment_proof,
                'payment_proof_url' => $first->payment_proof
                    ? Storage::disk('public')->url($first->payment_proof)
                    : null,
                'payment_proof_uploaded_at' => $first->payment_proof_uploaded_at?->format('d/m/Y H:i'),
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
                'alamat' => $first->alamat,
                'outlet_name' => $first->outlet_name,
                'outlet_city' => $first->outlet_city,
                'outlet_district' => $first->outlet_district,
                'outlet_address' => $first->outlet_address_snapshot,
                'outlet_label' => $this->buildOutletLabel($first->outlet_name, $first->outlet_district, $first->outlet_city),
                'payment_method' => $first->payment_method ?? 'manual',
                'payment_method_label' => $this->paymentMethodLabel($first->payment_method),
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
        float|int $grandTotal,
        string $paymentMethod,
        Outlet $outlet
    ): string {
        $itemLines = collect($items)->map(function ($item) {
            return '- ' . $item['pesanan_item'] . ' x' . $item['jumlah'];
        })->implode("\n");

        $shoppingLabel = ucwords(str_replace('-', ' ', $shoppingType));
        $paymentMethodLabel = $this->paymentMethodLabel($paymentMethod);
        $outletLabel = $this->buildOutletLabel($outlet->name, $outlet->district, $outlet->city);

        return "Halo Admin, saya sudah membuat pesanan {$orderCode} sebesar Rp" . number_format($grandTotal, 0, ',', '.') . ".\n"
            . "Nama: {$customerName}\n"
            . "No. WA: {$phoneNumber}\n"
            . "Jenis Belanja: {$shoppingLabel}\n"
            . "Outlet: {$outletLabel}\n"
            . "Metode Pembayaran: {$paymentMethodLabel}\n"
            . "Alamat: {$address}\n"
            . "Detail Pesanan:\n{$itemLines}\n"
            . "Mohon dibantu untuk konfirmasi pembayarannya ya.";
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

    private function mergePaymentSettingsWithDemoDefaults(array $settings): array
    {
        $filteredSettings = array_filter($settings, fn ($value) => ! blank($value));

        return array_merge($this->demoPaymentDefaults(), $filteredSettings);
    }

    private function buildPaymentDetails(string $paymentMethod, array $settings): array
    {
        if ($paymentMethod === 'qris') {
            return [
                'type' => 'qris',
                'label' => $this->settingValue($settings, 'payment_qris_label', 'Demo QRIS Chi-Pok'),
                'image_url' => $this->settingValue($settings, 'payment_qris_image_url'),
                'note' => $this->settingValue($settings, 'payment_qris_note', 'Ini hanya QRIS demo untuk simulasi checkout.'),
            ];
        }

        return [
            'type' => 'bank_transfer',
            'bank_name' => $this->settingValue($settings, 'payment_bank_name', 'Bank Demo'),
            'account_number' => $this->settingValue($settings, 'payment_bank_account_number', '1234567890'),
            'account_name' => $this->settingValue($settings, 'payment_bank_account_name', 'Chi Pok Demo'),
            'note' => $this->settingValue($settings, 'payment_bank_note', 'Ini hanya rekening demo untuk simulasi pembayaran.'),
        ];
    }

    private function buildPaymentInstructions(
        string $paymentMethod,
        array $paymentDetails,
        float|int $grandTotal,
        string $orderCode
    ): array {
        $formattedTotal = 'Rp' . number_format($grandTotal, 0, ',', '.');
        $instructions = [
            'Ini hanya demo pembayaran, belum terhubung ke payment gateway live.',
            "Bayarkan pesanan {$orderCode} sebesar {$formattedTotal}.",
        ];

        if ($paymentMethod === 'qris') {
            $instructions[] = ! empty($paymentDetails['image_url'])
                ? 'Scan tampilan QRIS demo yang ditampilkan lalu lanjutkan simulasi pembayaran.'
                : 'Gunakan tampilan placeholder QRIS demo yang muncul di halaman ini untuk simulasi pembayaran.';
        }

        if ($paymentMethod === 'bank_transfer') {
            $instructions[] = 'Gunakan rekening demo yang ditampilkan untuk simulasi transfer bank.';
        }

        if (! empty($paymentDetails['note'])) {
            $instructions[] = $paymentDetails['note'];
        }

        $instructions[] = 'Setelah simulasi pembayaran selesai, admin bisa mengonfirmasi pembayaran secara manual untuk melanjutkan pesanan.';

        return $instructions;
    }

    private function demoPaymentDefaults(): array
    {
        return [
            'payment_qris_label' => 'Demo QRIS Chi-Pok',
            'payment_qris_image_url' => null,
            'payment_qris_note' => 'Ini hanya QRIS demo untuk simulasi checkout.',
            'payment_bank_name' => 'Bank Demo',
            'payment_bank_account_number' => '1234567890',
            'payment_bank_account_name' => 'Chi Pok Demo',
            'payment_bank_note' => 'Ini hanya rekening demo untuk simulasi pembayaran.',
        ];
    }

    private function settingValue(array $settings, string $key, ?string $default = null): ?string
    {
        return filled($settings[$key] ?? null)
            ? (string) $settings[$key]
            : $default;
    }

    private function requiresDeliveryAddress(string $shoppingType): bool
    {
        return $this->normalizeShoppingType($shoppingType) === 'delivery';
    }

    private function resolveOrderAddress(string $shoppingType, mixed $address): string
    {
        $normalizedAddress = trim((string) $address);

        if ($this->requiresDeliveryAddress($shoppingType)) {
            return $normalizedAddress;
        }

        return match ($this->normalizeShoppingType($shoppingType)) {
            'dine in' => 'Makan di tempat',
            'take away', 'take-away', 'takeaway' => 'Ambil di outlet',
            default => $normalizedAddress !== '' ? $normalizedAddress : '-',
        };
    }

    private function normalizeShoppingType(string $shoppingType): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $shoppingType)));
    }

    private function checkoutLockKey(?int $userId, string $clientRequestId): string
    {
        return 'checkout-lock:' . ($userId ?? 'guest') . ':' . $clientRequestId;
    }

    private function checkoutResponseCacheKey(?int $userId, string $clientRequestId): string
    {
        return 'checkout-response:' . ($userId ?? 'guest') . ':' . $clientRequestId;
    }
}
