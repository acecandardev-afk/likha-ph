<?php

namespace App\Http\Controllers\Customer;

use App\Models\DeliveryReport;
use App\Models\OrderPackage;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class DeliveryReportController extends CustomerController
{
    public function create(OrderPackage $orderPackage)
    {
        $orderPackage->load('order');
        abort_unless($orderPackage->order->customer_id === $this->getCustomer()->id, 403);

        return view('customer.delivery-reports.create', compact('orderPackage'));
    }

    public function store(Request $request, OrderPackage $orderPackage, ImageUploadService $imageUploadService)
    {
        $orderPackage->load('order');
        abort_unless($orderPackage->order->customer_id === $this->getCustomer()->id, 403);

        $validated = $request->validate([
            'concern' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:2000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
        ]);

        $report = DeliveryReport::create([
            'order_package_id' => $orderPackage->id,
            'user_id' => $this->getCustomer()->id,
            'concern' => $validated['concern'],
            'details' => $validated['details'] ?? null,
            'status' => DeliveryReport::STATUS_OPEN,
        ]);

        if ($request->hasFile('proof_image')) {
            $filename = $imageUploadService->uploadDeliveryReportProof($request->file('proof_image'), $report->id);
            $report->update(['proof_image' => $filename]);
        }

        return redirect()
            ->route('customer.orders.show', $orderPackage->order)
            ->with('success', 'Your delivery concern was submitted. Admin will review it.');
    }
}
