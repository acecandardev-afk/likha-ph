<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderFinancialDispute;
use Illuminate\Http\Request;

class FinancialDisputeController extends AdminController
{
    public function index()
    {
        $disputes = OrderFinancialDispute::query()
            ->with(['order', 'user', 'orderPackage'])
            ->latest()
            ->paginate(25);

        return view('admin.financial-disputes.index', compact('disputes'));
    }

    public function resolve(Request $request, OrderFinancialDispute $dispute)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.OrderFinancialDispute::STATUS_UNDER_REVIEW.','.OrderFinancialDispute::STATUS_RESOLVED.','.OrderFinancialDispute::STATUS_REJECTED],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $dispute->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? null,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Dispute updated.');
    }
}
