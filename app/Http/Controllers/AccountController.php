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
        $deliveryCity = Guihulngan::deliveryCity();
        if (! $deliveryCity) {
            return view('account.edit', [
                'user' => $user,
                'addressUnavailable' => true,
            ]);
        }

        $deliveryCity->loadMissing('province.region');
        $barangays = $deliveryCity->barangays()->orderBy('name')->get(['id', 'name', 'code']);
        $delivery = [
            'region_id' => $deliveryCity->province->region_id,
            'province_id' => $deliveryCity->province_id,
            'city_id' => $deliveryCity->id,
            'region_name' => $deliveryCity->province->region->name,
            'province_name' => $deliveryCity->province->name,
            'city_name' => $deliveryCity->name,
        ];

        $selectedBarangayId = old('barangay');
        if ($selectedBarangayId === null) {
            $u = $user->barangay;
            if ($u !== null && $u !== '') {
                if (is_numeric($u) && $barangays->contains('id', (int) $u)) {
                    $selectedBarangayId = (int) $u;
                } else {
                    $selectedBarangayId = $barangays->firstWhere('name', (string) $u)?->id;
                }
            }
        } elseif (is_string($selectedBarangayId)) {
            $selectedBarangayId = is_numeric($selectedBarangayId)
                ? (int) $selectedBarangayId
                : $barangays->firstWhere('name', $selectedBarangayId)?->id;
        }

        return view('account.edit', compact(
            'user',
            'delivery',
            'barangays',
            'selectedBarangayId'
        ));
    }

    public function update(Request $request)
    {
        $city = Guihulngan::deliveryCity();
        if (! $city) {
            $validated = $request->validate([
                'country' => 'required|string|in:Philippines',
                'street_address' => 'nullable|string|max:500',
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^(09\d{9}|\+63\d{10})$/',
                    'max:13'
                ],
            ]);

            $request->user()->update($validated);

            return back()->with('success', 'Contact details updated. Full address will be available once location data is configured.');
        }

        $validated = $request->validate([
            'country' => 'required|string|in:Philippines',
            'barangay' => Guihulngan::guihulnganBarangayIdRulesOptional(),
            'street_address' => 'nullable|string|max:500',
            'phone' => [
                'nullable',
                'string',
                'regex:/^(09\d{9}|\+63\d{10})$/',
                'max:13'
            ],
        ]);

        $city->loadMissing('province.region');
        $validated['region'] = (int) $city->province->region_id;
        $validated['province'] = (int) $city->province_id;
        $validated['city'] = (int) $city->id;

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
