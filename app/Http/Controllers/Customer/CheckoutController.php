<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\CheckoutRequest;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Services\CartService;
use App\Services\OrderService;

class CheckoutController extends CustomerController
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {
        parent::__construct();
    }

    public function index()
    {
        $customer = $this->getCustomer();
        $summary = $this->cartService->getCartSummary($customer);

        if ($summary['total_items'] === 0) {
            return redirect()->route('customer.cart.index')
                ->withErrors(['error' => 'Your cart is empty.']);
        }

        // Validate cart
        $errors = $this->cartService->validateCart($customer);
        if (!empty($errors)) {
            return redirect()->route('customer.cart.index')
                ->withErrors($errors);
        }

        $selectedRegionId = old('region');
        if ($selectedRegionId === null && ! empty($customer->region)) {
            $selectedRegionId = Region::query()->where('name', $customer->region)->value('id');
        }

        $selectedProvinceId = old('province');
        if ($selectedProvinceId === null && ! empty($customer->province)) {
            $selectedProvinceId = Province::query()->where('name', $customer->province)->value('id');
        }

        $selectedCityId = old('city');
        if ($selectedCityId === null && ! empty($customer->city)) {
            $selectedCityId = City::query()->where('name', $customer->city)->value('id');
        }

        $selectedBarangayId = old('barangay');
        if ($selectedBarangayId === null && ! empty($customer->barangay)) {
            $selectedBarangayId = Barangay::query()->where('name', $customer->barangay)->value('id');
        }

        return view('customer.checkout.index', compact(
            'summary',
            'selectedRegionId',
            'selectedProvinceId',
            'selectedCityId',
            'selectedBarangayId'
        ));
    }

    public function store(CheckoutRequest $request)
    {
        $validated = $request->validated();

        try {
            $resolvedAddress = $this->resolveLocationNames($validated);

            $orders = $this->orderService->createOrdersFromCart(
                $this->getCustomer(),
                $resolvedAddress['payment_method'],
                $resolvedAddress['country'],
                $resolvedAddress['region'],
                $resolvedAddress['province'],
                $resolvedAddress['city'],
                $resolvedAddress['barangay'],
                $resolvedAddress['street_address'] ?? null,
                $resolvedAddress['phone'],
                $resolvedAddress['customer_notes'] ?? null,
                $validated['package_split'] ?? null
            );

            return redirect()
                ->route('customer.orders.index')
                ->with('success', count($orders) . ' order(s) placed successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
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