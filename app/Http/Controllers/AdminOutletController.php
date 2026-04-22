<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;

class AdminOutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.outlet', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateOutlet($request);

        Outlet::create($validated);

        return redirect()
            ->route('admin.outlet')
            ->with('success', 'Outlet berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $this->validateOutlet($request);

        $outlet->update($validated);

        return redirect()
            ->route('admin.outlet')
            ->with('success', 'Outlet berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();

        return redirect()
            ->route('admin.outlet')
            ->with('success', 'Outlet berhasil dihapus.');
    }

    private function validateOutlet(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'province' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:30',
            'maps_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['phone'] = $this->normalizePhone($validated['phone'] ?? null);

        return $validated;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);

        return $phone === '' ? null : $phone;
    }
}
