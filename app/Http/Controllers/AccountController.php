<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Support\Guihulngan;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = auth()->user();
        return view('account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|in:Philippines',
            'region' => 'nullable|integer|exists:regions,id',
            'province' => 'nullable|integer|exists:provinces,id',
            'city' => 'nullable|integer|exists:cities,id',
            'barangay' => 'nullable|integer|exists:barangays,id',
            'street_address' => 'nullable|string|max:500',
            'phone' => [
                'nullable',
                'string',
                'regex:/^(09\d{9}|\+63\d{10})$/',
                'max:13'
            ],
        ]);

        $request->user()->update($this->resolveLocationNames($validated));

        return back()->with('success', 'Shipping address updated successfully.');
    }

    protected function resolveLocationNames(array $validated): array
    {
        if (!empty($validated['region'])) {
            $validated['region'] = Region::find($validated['region'])->name ?? $validated['region'];
        }

        if (!empty($validated['province'])) {
            $validated['province'] = Province::find($validated['province'])->name ?? $validated['province'];
        }

        if (!empty($validated['city'])) {
            $validated['city'] = City::find($validated['city'])->name ?? $validated['city'];
        }

        if (!empty($validated['barangay'])) {
            $validated['barangay'] = Barangay::find($validated['barangay'])->name ?? $validated['barangay'];
        }

        return $validated;
    }
}
