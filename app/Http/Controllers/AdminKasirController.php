<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminKasirController extends Controller
{
    public function index(): View
    {
        $kasirs = User::with('outlet')
            ->where('role', 'kasir')
            ->orderBy('created_at', 'desc')
            ->get();

        $outlets = Outlet::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.kasir', [
            'kasirs' => $kasirs,
            'outlets' => $outlets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateKasir($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'kasir',
            'outlet_id' => $validated['outlet_id'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);

        return redirect()
            ->route('admin.kasir')
            ->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function update(Request $request, User $kasir): RedirectResponse
    {
        abort_unless($kasir->isKasir(), 404);

        $validated = $this->validateKasir($request, $kasir);

        $kasir->fill([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'],
            'outlet_id' => $validated['outlet_id'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $kasir->password = Hash::make($validated['password']);
        }

        $kasir->save();

        return redirect()
            ->route('admin.kasir')
            ->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function destroy(User $kasir): RedirectResponse
    {
        abort_unless($kasir->isKasir(), 404);

        $kasir->delete();

        return redirect()
            ->route('admin.kasir')
            ->with('success', 'Akun kasir berhasil dihapus.');
    }

    private function validateKasir(Request $request, ?User $kasir = null): array
    {
        $kasirId = $kasir?->id;

        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($kasirId),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($kasirId),
            ],
            'password' => [$kasir ? 'nullable' : 'required', 'string', 'min:4'],
            'outlet_id' => [
                'required',
                'integer',
                'exists:outlets,id',
                Rule::unique('users', 'outlet_id')
                    ->where(fn ($query) => $query->where('role', 'kasir'))
                    ->ignore($kasirId),
            ],
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:1000',
        ], [
            'outlet_id.unique' => 'Outlet ini sudah dipakai oleh kasir lain. Pilih outlet lain atau edit akun kasir yang sudah ada.',
        ]);
    }
}
