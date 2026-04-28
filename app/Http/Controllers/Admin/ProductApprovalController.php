<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductApproval;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApprovalController extends AdminController
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Display pending products for approval.
     */
    public function index()
    {
        $pendingProducts = Product::pending()
            ->with(['artisan.artisanProfile', 'category', 'images'])
            ->latest()
            ->paginate(20);

        return view('admin.products.pending', compact('pendingProducts'));
    }

    /**
     * Show product details for review.
     */
    public function show(Product $product)
    {
        $this->authorize('approve', $product);

        $product->load(['artisan.artisanProfile', 'category', 'images', 'approvals.reviewer']);

        return view('admin.products.review', compact('product'));
    }

    /**
     * Approve a product.
     */
    public function approve(Request $request, Product $product)
    {
        $this->authorize('approve', $product);

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $product) {
            $product->update([
                'approval_status' => 'approved',
                'rejection_reason' => null,
            ]);

            ProductApproval::create([
                'product_id' => $product->id,
                'reviewed_by' => auth()->id(),
                'status' => 'approved',
                'notes' => $request->notes,
                'reviewed_at' => now(),
            ]);
        });

        $product = $product->fresh();
        AuditLog::record('listing.approved', 'Published '.$product->name.' to the shop.', $product);

        $this->notificationService->notifyProductApproved($product);

        return redirect()
            ->route('admin.products.pending')
            ->with('success', "Product '{$product->name}' has been approved.");
    }

    /**
     * Reject a product.
     */
    public function reject(Request $request, Product $product)
    {
        $this->authorize('approve', $product);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $product) {
            $product->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);

            ProductApproval::create([
                'product_id' => $product->id,
                'reviewed_by' => auth()->id(),
                'status' => 'rejected',
                'notes' => $request->reason,
                'reviewed_at' => now(),
            ]);
        });

        $product = $product->fresh();
        AuditLog::record('listing.rejected', 'Sent '.$product->name.' back to the seller for updates.', $product);

        $this->notificationService->notifyProductRejected($product);

        return redirect()
            ->route('admin.products.pending')
            ->with('success', "Product '{$product->name}' has been rejected.");
    }

    /**
     * Display all approved products.
     */
    public function approved()
    {
        $approvedProducts = Product::approved()
            ->with(['artisan.artisanProfile', 'category', 'images'])
            ->latest()
            ->paginate(20);

        return view('admin.products.approved', compact('approvedProducts'));
    }

    /**
     * Display all rejected products.
     */
    public function rejected()
    {
        $rejectedProducts = Product::where('approval_status', 'rejected')
            ->with(['artisan.artisanProfile', 'category', 'images'])
            ->latest()
            ->paginate(20);

        return view('admin.products.rejected', compact('rejectedProducts'));
    }
}
