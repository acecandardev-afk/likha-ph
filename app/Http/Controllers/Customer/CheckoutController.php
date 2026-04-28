<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\CheckoutRequest;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

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
        if (! empty($errors)) {
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

        $vc = old('voucher_code');
        $checkoutPreview = $this->orderService->previewCheckoutTotals(
            $customer,
            ($vc !== null && trim((string) $vc) !== '') ? trim((string) $vc) : null
        );

        return view('customer.checkout.index', compact(
            'summary',
            'checkoutPreview',
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
                $validated['package_split'] ?? null,
                $validated['voucher_code'] ?? null
            );

            return redirect()
                ->route('customer.orders.index')
                ->with('success', count($orders).' order(s) placed successfully!');
        } catch (\InvalidArgumentException $e) {
            if ($e->getMessage() === 'online_checkout_cod_only') {
                return back()->withInput()->with('error', 'Pay when your order arrives is the only option for this checkout.');
            }
            if ($e->getMessage() === 'voucher_invalid') {
                return back()
                    ->withInput()
                    ->withErrors(['voucher_code' => 'That promo could not be applied. Please check the code.']);
            }

            Log::warning('checkout_failed', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'We couldn’t place your order. Please check your details and try again.');
        } catch (\Exception $e) {
            Log::warning('checkout_failed', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'We couldn’t place your order. Please check your details and try again.');
        }
    }

    protected function resolveLocationNames(array $validated): array
    {
        if (! empty($validated['region'])) {
            $validated['region'] = Region::find($validated['region'])?->name ?? (string) $validated['region'];
        }

        if (! empty($validated['province'])) {
            $validated['province'] = Province::find($validated['province'])?->name ?? (string) $validated['province'];
        }

        if (! empty($validated['city'])) {
            $validated['city'] = City::find($validated['city'])?->name ?? (string) $validated['city'];
        }

        if (! empty($validated['barangay'])) {
            $validated['barangay'] = Barangay::find($validated['barangay'])?->name ?? (string) $validated['barangay'];
        }

        return $validated;
    }
}
