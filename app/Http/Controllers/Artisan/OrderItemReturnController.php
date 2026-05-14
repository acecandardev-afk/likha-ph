<?php

namespace App\Http\Controllers\Artisan;

use App\Models\OrderItemReturn;
use Illuminate\Http\Request;

class OrderItemReturnController extends ArtisanController
{
    public function index(Request $request)
    {
        $artisan = $this->getArtisan();

        $query = OrderItemReturn::query()
            ->where('artisan_id', $artisan->id)
            ->with(['order', 'orderItem.product', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('artisan.returns.index', compact('returns'));
    }

    public function show(OrderItemReturn $orderItemReturn)
    {
        $this->authorize('view', $orderItemReturn);

        $orderItemReturn->load([
            'order.customer',
            'orderItem.product',
            'customer',
            'reviewer',
        ]);

        return view('artisan.returns.show', compact('orderItemReturn'));
    }
}
