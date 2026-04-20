<?php

namespace App\Http\Controllers;

use App\Support\Guihulngan;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = auth()->user();
        return view('account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shipping_barangay' => Guihulngan::barangayRules(false),
            'shipping_address' => 'nullable|string|max:500',
            'shipping_phone' => 'nullable|string|max:20',
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Shipping address updated successfully.');
    }
}
