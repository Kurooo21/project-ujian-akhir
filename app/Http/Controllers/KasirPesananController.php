<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithGroupedOrders;
use App\Models\Ingredient;
use App\Models\Pesanan;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KasirPesananController extends Controller
{
    use InteractsWithGroupedOrders;

    public function index()
    {
        $kasir = Auth::user()->loadMissing('outlet');
        $assignedOutlet = $kasir->outlet;

        $orders = $assignedOutlet
            ? $this->summarizeGroupedOrders(
                Pesanan::with('user')
                    ->where('outlet_id', $assignedOutlet->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy(fn ($item) => $this->buildGroupId($item))
            )
            : collect();

        return view('kasir.pesanan', compact('orders', 'assignedOutlet'));
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        if (! Auth::user()->outlet_id) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Akun kasir ini belum ditautkan ke outlet. Hubungi admin untuk mengatur outlet kerja.');
        }

        $request->validate([
            'group_id' => 'required|string',
            'status' => [
                'required',
                'string',
                Rule::in(['Diproses', 'Pesanan Siap', 'Sedang Diantar', 'Selesai', 'Dibatalkan']),
            ],
        ]);

        $ordersQuery = $this->queryByGroupId($request->group_id)
            ->where('outlet_id', Auth::user()->outlet_id);

        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pesanan tidak ditemukan untuk outlet kasir ini.');
        }

        $firstOrder = $orders->first();

        if (($firstOrder->payment_status ?? 'Lunas') !== 'Lunas') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pembayaran belum dikonfirmasi. Konfirmasi pembayaran dulu sebelum mengubah status pesanan.');
        }

        $ordersQuery->update(['status' => $request->status]);

        return redirect()
            ->route('kasir.pesanan')
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function confirmPayment(Request $request): RedirectResponse
    {
        if (! Auth::user()->outlet_id) {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Akun kasir ini belum ditautkan ke outlet. Hubungi admin untuk mengatur outlet kerja.');
        }

        $request->validate([
            'group_id' => 'required|string',
        ]);

        $result = DB::transaction(function () use ($request) {
            $orders = $this->queryByGroupId($request->group_id)
                ->where('outlet_id', Auth::user()->outlet_id)
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return [
                    'status' => 'not_found',
                ];
            }

            $firstOrder = $orders->first();

            if (($firstOrder->payment_status ?? 'Lunas') === 'Lunas') {
                return [
                    'status' => 'already_paid',
                ];
            }

            $inventoryError = $this->deductIngredientsForOrders($orders);

            if ($inventoryError !== null) {
                return [
                    'status' => 'inventory_error',
                    'message' => $inventoryError,
                ];
            }

            Pesanan::whereIn('id', $orders->pluck('id'))
                ->update([
                    'payment_status' => 'Lunas',
                    'paid_at' => now(),
                    'status' => 'Diproses',
                ]);

            return [
                'status' => 'success',
            ];
        });

        if ($result['status'] === 'not_found') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', 'Pesanan tidak ditemukan untuk outlet kasir ini.');
        }

        if ($result['status'] === 'already_paid') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('success', 'Pembayaran pesanan ini sudah pernah dikonfirmasi sebelumnya.');
        }

        if ($result['status'] === 'inventory_error') {
            return redirect()
                ->route('kasir.pesanan')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('kasir.pesanan')
            ->with('success', 'Pembayaran berhasil dikonfirmasi dan pesanan masuk ke proses dapur.');
    }

    private function deductIngredientsForOrders(Collection $orders): ?string
    {
        $ingredientDeductions = $this->buildIngredientDeductions($orders);

        if ($ingredientDeductions->isEmpty()) {
            return null;
        }

        $ingredients = Ingredient::query()
            ->whereIn('id', $ingredientDeductions->keys())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($ingredientDeductions as $ingredientId => $requiredQuantity) {
            /** @var Ingredient|null $ingredient */
            $ingredient = $ingredients->get($ingredientId);

            if (! $ingredient) {
                return 'Ada bahan resep yang tidak ditemukan. Cek ulang data resep produk sebelum konfirmasi pembayaran.';
            }

            if ((float) $ingredient->stock_quantity < $requiredQuantity) {
                return sprintf(
                    'Stok bahan %s tidak cukup. Dibutuhkan %s %s, sisa stok %s %s.',
                    $ingredient->name,
                    $this->formatIngredientQuantity($requiredQuantity),
                    $ingredient->unit,
                    $this->formatIngredientQuantity((float) $ingredient->stock_quantity),
                    $ingredient->unit
                );
            }
        }

        foreach ($ingredientDeductions as $ingredientId => $requiredQuantity) {
            /** @var Ingredient $ingredient */
            $ingredient = $ingredients->get($ingredientId);

            $ingredient->stock_quantity = max(
                (float) $ingredient->stock_quantity - $requiredQuantity,
                0
            );
            $ingredient->save();
        }

        return null;
    }

    private function buildIngredientDeductions(Collection $orders): Collection
    {
        $productsByName = Product::with('recipeItems.ingredient')
            ->get()
            ->keyBy(fn (Product $product) => $this->normalizeProductName($product->name));

        return $orders->reduce(function (Collection $carry, Pesanan $order) use ($productsByName) {
            /** @var Product|null $product */
            $product = $productsByName->get($this->normalizeProductName((string) $order->pesanan));

            if (! $product) {
                return $carry;
            }

            $orderQuantity = max((float) $order->jumlah, 0);

            foreach ($product->recipeItems as $recipeItem) {
                if (! $recipeItem->ingredient || $recipeItem->quantity_required <= 0) {
                    continue;
                }

                $ingredientId = (int) $recipeItem->ingredient_id;
                $carry->put(
                    $ingredientId,
                    (float) $carry->get($ingredientId, 0) + ($orderQuantity * (float) $recipeItem->quantity_required)
                );
            }

            return $carry;
        }, collect())->filter(fn ($quantity) => $quantity > 0);
    }

    private function normalizeProductName(string $productName): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $productName)));
    }

    private function formatIngredientQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
