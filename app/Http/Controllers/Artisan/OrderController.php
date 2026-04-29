<?php

namespace App\Http\Controllers\Artisan;

use App\Models\Order;
use App\Models\SellerCodHandoff;
use App\Services\LedgerSettlementReader;
use App\Services\OrderService;
use App\Services\RiderSettlementService;
use Illuminate\Http\Request;

class OrderController extends ArtisanController
{
    /**
     * Display artisan's orders.
     */
    public function index(Request $request)
    {
        $artisan = $this->getArtisan();

        $query = $artisan->artisanOrders()
            ->with(['customer', 'items.product', 'payment', 'rider']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('artisan.orders.index', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function show(Order $order, LedgerSettlementReader $ledgerReader, RiderSettlementService $riderSettlement)
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'items.product.images',
            'payment',
            'messages.sender',
            'rider',
            'packages.rider',
            'packages.items.orderItem',
            'sellerCodHandoff',
            'deliverySettlementJournal.lines',
        ]);

        $ledgerSnapshot = $ledgerReader->snapshotForOrder($order);

        $packageAllocations = [];
        foreach ($order->packages as $pkg) {
            $packageAllocations[$pkg->id] = $riderSettlement->allocatePackage($pkg);
        }

        return view('artisan.orders.show', compact('order', 'ledgerSnapshot', 'packageAllocations'));
    }

    /**
     * Seller confirms physical receipt of goods share from rider handoff (aligned with ledger when posted).
     */
    public function storeCodHandoff(Request $request, Order $order, LedgerSettlementReader $ledgerReader)
    {
        $this->authorize('view', $order);

        if (! $order->isDelivered()) {
            return back()->withErrors(['cod' => 'You can confirm rider handoff after the order is fully delivered.']);
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $ledger = $ledgerReader->snapshotForOrder($order);
        $expected = $ledger['artisan_payable'] ?? round((float) $order->artisanMerchandiseShare(), 2);

        SellerCodHandoff::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'artisan_user_id' => $order->artisan_id,
                'ledger_journal_id' => $ledger['journal_id'] ?? null,
                'expected_artisan_payable' => $expected,
                'acknowledged_at' => now(),
                'note' => $validated['note'] ?? null,
            ]
        );

        return back()->with('success', 'Recorded your confirmation about rider settlement for your goods.');
    }

    /**
     * Approve a pending order.
     */
    public function approve(Order $order, OrderService $orderService)
    {
        $this->authorize('approve', $order);

        if (! $order->canBeApproved()) {
            return back()->withErrors(['error' => 'Order cannot be approved at this time.']);
        }

        $order->update([
            'status' => 'shipped',
            'approved_at' => now(),
        ]);

        $orderService->assignRidersAfterSellerApproval($order->fresh());

        return back()->with('success', 'Order approved and marked as shipped. A rider will be assigned automatically when available.');
    }

    /**
     * Mark order as completed.
     */
    public function complete(Order $order)
    {
        $this->authorize('complete', $order);

        if (! $order->canBeCompleted()) {
            return back()->withErrors(['error' => 'Order cannot be completed at this time.']);
        }

        $order->update(['status' => 'completed']);

        return back()->with('success', 'Order marked as completed.');
    }
}
