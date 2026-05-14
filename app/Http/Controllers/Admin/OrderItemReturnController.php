<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderItemReturn;
use App\Services\OrderItemReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderItemReturnController extends AdminController
{
    public function index(Request $request)
    {
        $query = OrderItemReturn::query()
            ->with(['order', 'orderItem.product', 'customer', 'artisan']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $returns = $query->latest()->paginate(25)->withQueryString();

        return view('admin.order-returns.index', compact('returns'));
    }

    public function show(OrderItemReturn $orderItemReturn)
    {
        $orderItemReturn->load([
            'order.customer',
            'order.artisan',
            'orderItem.product',
            'customer',
            'artisan',
            'reviewer',
        ]);

        return view('admin.order-returns.show', compact('orderItemReturn'));
    }

    public function approve(Request $request, OrderItemReturn $orderItemReturn, OrderItemReturnService $returnService)
    {
        $this->authorize('approve', $orderItemReturn);

        $validated = $request->validate([
            'admin_resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $returnService->approve(
                $orderItemReturn,
                $request->user(),
                $validated['admin_resolution_notes'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['return' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::warning('order_item_return_approve_failed', ['message' => $e->getMessage()]);

            return back()->with('error', 'Unable to approve this return. Please try again.');
        }

        return back()->with('success', 'Return approved. Stock was increased for the product when a product record exists.');
    }

    public function reject(Request $request, OrderItemReturn $orderItemReturn, OrderItemReturnService $returnService)
    {
        $this->authorize('reject', $orderItemReturn);

        $validated = $request->validate([
            'admin_resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $returnService->reject(
                $orderItemReturn,
                $request->user(),
                $validated['admin_resolution_notes'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['return' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::warning('order_item_return_reject_failed', ['message' => $e->getMessage()]);

            return back()->with('error', 'Unable to reject this return. Please try again.');
        }

        return back()->with('success', 'Return marked as rejected.');
    }
}
