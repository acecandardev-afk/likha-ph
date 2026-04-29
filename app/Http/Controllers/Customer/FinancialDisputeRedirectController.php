<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

/**
 * POST submits financial dispute; GET redirects so bookmarks don't 404.
 */
class FinancialDisputeRedirectController extends Controller
{
    public function __invoke(Order $order): RedirectResponse
    {
        return redirect()->route('customer.orders.show', $order);
    }
}
