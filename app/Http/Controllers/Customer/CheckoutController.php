<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\CheckoutRequest;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\Guihulngan;

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

        $deliveryCity = Guihulngan::deliveryCity();
        if (! $deliveryCity) {
            return redirect()->route('customer.cart.index')
                ->withErrors(['error' => 'Delivery location is not available. Please try again later.']);
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
            $u = $customer->barangay;
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

        return view('customer.checkout.index', compact('summary', 'delivery', 'barangays', 'selectedBarangayId'));
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
                $resolvedAddress['customer_notes'] ?? null
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