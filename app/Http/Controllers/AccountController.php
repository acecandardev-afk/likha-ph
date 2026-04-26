<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = auth()->user();
        $selectedRegionId = old('region');
        if ($selectedRegionId === null && ! empty($user->region)) {
            $selectedRegionId = Region::query()->where('name', $user->region)->value('id');
        }

        $selectedProvinceId = old('province');
        if ($selectedProvinceId === null && ! empty($user->province)) {
            $selectedProvinceId = Province::query()->where('name', $user->province)->value('id');
        }

        $selectedCityId = old('city');
        if ($selectedCityId === null && ! empty($user->city)) {
            $selectedCityId = City::query()->where('name', $user->city)->value('id');
        }

        $selectedBarangayId = old('barangay');
        if ($selectedBarangayId === null && ! empty($user->barangay)) {
            $selectedBarangayId = Barangay::query()->where('name', $user->barangay)->value('id');
        }

        return view('account.edit', compact('user', 'selectedRegionId', 'selectedProvinceId', 'selectedCityId', 'selectedBarangayId'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|in:Philippines',
            'region' => ['required', 'integer', 'exists:regions,id'],
            'province' => [
                'required',
                'integer',
                Rule::exists('provinces', 'id')->where(fn ($q) => $q->where('region_id', (int) $request->input('region'))),
            ],
            'city' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(fn ($q) => $q->where('province_id', (int) $request->input('province'))),
            ],
            'barangay' => [
                'required',
                'integer',
                Rule::exists('barangays', 'id')->where(fn ($q) => $q->where('city_id', (int) $request->input('city'))),
            ],
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
