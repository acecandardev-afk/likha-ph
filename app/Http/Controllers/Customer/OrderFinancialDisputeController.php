<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use App\Models\OrderFinancialDispute;
use App\Models\OrderPackage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderFinancialDisputeController extends CustomerController
{
    public function store(Request $request, Order $order, NotificationService $notificationService)
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in([
                OrderFinancialDispute::CATEGORY_COD_PARTIAL,
                OrderFinancialDispute::CATEGORY_REFUND,
                OrderFinancialDispute::CATEGORY_RIDER_PAYMENT,
                OrderFinancialDispute::CATEGORY_OTHER,
            ])],
            'description' => ['required', 'string', 'max:5000'],
            'order_package_id' => ['nullable', 'integer'],
        ]);

        $packageId = $validated['order_package_id'] ?? null;
        if ($packageId !== null) {
            $belongs = OrderPackage::query()
                ->where('id', $packageId)
                ->where('order_id', $order->id)
                ->exists();
            if (! $belongs) {
                return back()->withErrors(['order_package_id' => 'Invalid package for this order.']);
            }
        }

        $dispute = OrderFinancialDispute::create([
            'order_id' => $order->id,
            'order_package_id' => $packageId,
            'user_id' => $request->user()->id,
            'actor_role' => (string) $request->user()->role,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => OrderFinancialDispute::STATUS_OPEN,
        ]);

        $notificationService->notifyFinancialDisputeOpened($dispute);

        return back()->with('success', 'We recorded your concern. Support will review it.');
    }
}
