<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Sale;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends AdminController
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function index()
    {
        $sales = Sale::with(['user', 'items'])
            ->latest()
            ->paginate(20);

        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::approved()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock']);

        return view('admin.sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,gcash,card,other'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique();
        $products = Product::approved()->whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $totalAmount = 0;

        foreach ($validated['items'] as $row) {
            $product = $products->get($row['product_id']);
            if (! $product) {
                return back()->withErrors(['items' => 'One or more selected products are not available.'])->withInput();
            }
            if ($product->stock < $row['quantity']) {
                return back()->withErrors(['items' => "Insufficient stock for {$product->name}."])->withInput();
            }
            $qty = (int) $row['quantity'];
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $qty;
            $totalAmount += $totalPrice;
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        $amountPaid = (float) $validated['amount_paid'];
        $change = max(0, $amountPaid - $totalAmount);

        $sale = DB::transaction(function () use ($items, $totalAmount, $amountPaid, $change, $validated) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'change_amount' => $change,
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($items as $item) {
                $sale->items()->create($item);
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }

            return $sale;
        });

        foreach ($items as $item) {
            $product = Product::query()->find($item['product_id']);
            $product->refresh();

            if ($product->approval_status === 'approved') {
                if ($product->stock === 0) {
                    $this->notificationService->notifyOutOfStock($product);
                } elseif ($product->stock <= StockService::LOW_STOCK_THRESHOLD) {
                    $this->notificationService->notifyLowStock($product);
                }
            }
        }

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'Sale recorded. Receipt #'.$sale->receipt_number);
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'items']);

        return view('admin.sales.show', compact('sale'));
    }
}
