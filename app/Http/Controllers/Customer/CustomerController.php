<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'customer']);
    }

    /**
     * Get authenticated customer.
     */
    protected function getCustomer()
    {
        return auth()->user();
    }

    /**
     * Get common view data for customer panel.
     */
    protected function getCommonData(): array
    {
        return [
            'customer' => $this->getCustomer(),
        ];
    }
}
