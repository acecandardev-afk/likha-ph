<?php

namespace App\Http\Controllers\Admin;

use App\Models\DeliveryReport;
use Illuminate\Http\Request;

class DeliveryReportController extends AdminController
{
    public function index(Request $request)
    {
        $query = DeliveryReport::with(['orderPackage.order', 'reporter'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(25)->withQueryString();

        return view('admin.delivery-reports.index', compact('reports'));
    }

    public function show(DeliveryReport $deliveryReport)
    {
        $deliveryReport->load([
            'orderPackage.order.customer',
            'orderPackage.order.artisan',
            'orderPackage.rider',
            'reporter',
            'reviewer',
        ]);

        return view('admin.delivery-reports.show', compact('deliveryReport'));
    }

    public function update(Request $request, DeliveryReport $deliveryReport)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.DeliveryReport::STATUS_OPEN.','.DeliveryReport::STATUS_REVIEWED.','.DeliveryReport::STATUS_RESOLVED],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $deliveryReport->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Report updated.');
    }
}
