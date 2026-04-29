<?php

namespace App\Http\Controllers\Rider;

use App\Models\RiderRemittanceReport;
use Illuminate\Http\Request;

class RemittanceController extends RiderController
{
    public function store(Request $request)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($rider, 403);

        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'cod_declared_total' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'seller_pool_declared' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'platform_pool_declared' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        RiderRemittanceReport::query()->updateOrCreate(
            [
                'rider_id' => $rider->id,
                'report_date' => $validated['report_date'],
            ],
            [
                'cod_declared_total' => $validated['cod_declared_total'],
                'seller_pool_declared' => $validated['seller_pool_declared'] ?? null,
                'platform_pool_declared' => $validated['platform_pool_declared'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Saved. Thanks — your cash turn-in for that day is on record.');
    }
}
