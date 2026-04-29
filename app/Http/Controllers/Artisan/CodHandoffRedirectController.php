<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

/**
 * POST saves COD handoff; GET redirects so bookmarks don't 405 or hit closure/route-cache edge cases.
 */
class CodHandoffRedirectController extends Controller
{
    public function __invoke(Order $order): RedirectResponse
    {
        return redirect()->route('artisan.orders.show', $order);
    }
}
