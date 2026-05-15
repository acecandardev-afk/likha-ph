<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemReturn;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderItemReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function deliveredOrderWithLine(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $artisan = User::factory()->create(['role' => 'artisan', 'status' => 'active']);
        $admin = User::factory()->admin()->create();

        $category = Category::create([
            'name' => 'Test category',
            'description' => null,
        ]);

        $product = Product::create([
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'name' => 'Returnable craft',
            'description' => 'Test',
            'price' => 100,
            'stock' => 10,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-RET-'.strtoupper(substr(uniqid(), -10)),
            'customer_id' => $customer->id,
            'artisan_id' => $artisan->id,
            'subtotal' => 100,
            'platform_fee' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'status' => 'delivered',
            'country' => 'Philippines',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_description' => $product->description,
            'price' => 100,
            'quantity' => 3,
            'subtotal' => 300,
        ]);

        return compact('customer', 'artisan', 'admin', 'product', 'order', 'item');
    }

    public function test_customer_can_submit_return_with_proof(): void
    {
        Storage::fake('order_returns');

        ['customer' => $customer, 'order' => $order, 'item' => $item] = $this->deliveredOrderWithLine();

        $proof = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            [
                'quantity' => 2,
                'reason' => OrderItemReturn::REASON_DAMAGED,
                'notes' => 'The item arrived with visible damage on the corner and packaging was torn.',
                'proof_image' => $proof,
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('order_item_returns', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'customer_id' => $customer->id,
            'quantity' => 2,
            'reason' => OrderItemReturn::REASON_DAMAGED,
            'status' => OrderItemReturn::STATUS_PENDING_ADMIN,
        ]);

        $ret = OrderItemReturn::query()->where('order_item_id', $item->id)->first();
        $this->assertNotNull($ret);
        Storage::disk('order_returns')->assertExists($ret->proof_image);
    }

    public function test_admin_approve_restores_product_stock(): void
    {
        Storage::fake('order_returns');

        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $admin = $ctx['admin'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $product = $ctx['product'];

        $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            [
                'quantity' => 2,
                'reason' => OrderItemReturn::REASON_WRONG_ITEM,
                'notes' => 'I received a different color than what I ordered on the listing photo.',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ]
        );

        $ret = OrderItemReturn::query()->where('order_item_id', $item->id)->firstOrFail();
        $stockBefore = (int) $product->fresh()->stock;

        $this->actingAs($admin)->patch(
            route('admin.order-returns.approve', $ret),
            ['admin_resolution_notes' => 'Approved — stock adjusted.']
        )->assertSessionHas('success');

        $ret->refresh();
        $this->assertSame(OrderItemReturn::STATUS_APPROVED, $ret->status);
        $this->assertSame($stockBefore + 2, (int) $product->fresh()->stock);
        $this->assertNotNull($ret->stock_restored_at);
    }

    public function test_return_create_is_forbidden_when_order_pending(): void
    {
        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $order->update(['status' => 'pending']);

        $this->actingAs($customer)
            ->get(route('customer.orders.items.returns.create', [$order->fresh(), $item]))
            ->assertForbidden();
    }

    public function test_buyer_can_open_return_create_when_order_is_shipped(): void
    {
        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $order->update(['status' => 'shipped']);

        $this->actingAs($customer)
            ->get(route('customer.orders.items.returns.create', [$order->fresh(), $item]))
            ->assertOk();
    }

    public function test_buyer_can_open_return_create_for_legacy_confirmed_with_verified_payment(): void
    {
        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $order->update(['status' => 'confirmed']);
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'bank_transfer',
            'amount' => 100,
            'verification_status' => 'verified',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.orders.items.returns.create', [$order->fresh(), $item]))
            ->assertOk();
    }

    public function test_return_create_forbidden_for_confirmed_without_verified_payment(): void
    {
        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $order->update(['status' => 'confirmed']);
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'bank_transfer',
            'amount' => 100,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.orders.items.returns.create', [$order->fresh(), $item]))
            ->assertForbidden();
    }

    public function test_buyer_can_open_return_when_delivery_marked_delivered_even_if_status_stale(): void
    {
        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $order->update([
            'status' => 'confirmed',
            'delivery_status' => DeliveryService::STATUS_DELIVERED,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.orders.items.returns.create', [$order->fresh(), $item]))
            ->assertOk();
    }

    public function test_second_pending_return_for_same_line_is_rejected(): void
    {
        Storage::fake('order_returns');

        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $order = $ctx['order'];
        $item = $ctx['item'];

        $payload = [
            'quantity' => 1,
            'reason' => OrderItemReturn::REASON_MISSING_PARTS,
            'notes' => 'The set was missing the small cloth pouch that was shown in the listing photos.',
            'proof_image' => UploadedFile::fake()->image('proof.jpg'),
        ];

        $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            $payload
        )->assertRedirect();

        $this->actingAs($customer)->from(route('customer.orders.show', $order))->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            $payload
        )->assertSessionHasErrors('order');

        $this->assertSame(1, OrderItemReturn::query()->where('order_item_id', $item->id)->count());
    }

    public function test_admin_reject_does_not_restore_stock(): void
    {
        Storage::fake('order_returns');

        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $admin = $ctx['admin'];
        $order = $ctx['order'];
        $item = $ctx['item'];
        $product = $ctx['product'];

        $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            [
                'quantity' => 2,
                'reason' => OrderItemReturn::REASON_EXPIRED,
                'notes' => 'The consumable craft item was past the usable date printed on the inner label.',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ]
        );

        $ret = OrderItemReturn::query()->where('order_item_id', $item->id)->firstOrFail();
        $stockBefore = (int) $product->fresh()->stock;

        $this->actingAs($admin)->patch(
            route('admin.order-returns.reject', $ret),
            ['admin_resolution_notes' => 'Proof does not show batch or expiry clearly.']
        )->assertSessionHas('success');

        $ret->refresh();
        $this->assertSame(OrderItemReturn::STATUS_REJECTED, $ret->status);
        $this->assertSame($stockBefore, (int) $product->fresh()->stock);
        $this->assertNull($ret->stock_restored_at);
    }

    public function test_submit_creates_notifications_for_admin_and_artisan(): void
    {
        Storage::fake('order_returns');

        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $admin = $ctx['admin'];
        $artisan = $ctx['artisan'];
        $order = $ctx['order'];
        $item = $ctx['item'];

        $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            [
                'quantity' => 1,
                'reason' => OrderItemReturn::REASON_DAMAGED,
                'notes' => 'The woven panel has a loose thread and a stain visible under daylight.',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ]
        );

        $this->assertTrue(
            UserNotification::query()
                ->where('user_id', $admin->id)
                ->where('type', 'order_item_return_submitted')
                ->exists()
        );
        $this->assertTrue(
            UserNotification::query()
                ->where('user_id', $artisan->id)
                ->where('type', 'order_item_return_submitted_artisan')
                ->exists()
        );
    }

    public function test_artisan_can_view_own_return_other_artisan_forbidden(): void
    {
        Storage::fake('order_returns');

        $ctx = $this->deliveredOrderWithLine();
        $customer = $ctx['customer'];
        $artisan = $ctx['artisan'];
        $order = $ctx['order'];
        $item = $ctx['item'];

        $this->actingAs($customer)->post(
            route('customer.orders.items.returns.store', [$order, $item]),
            [
                'quantity' => 1,
                'reason' => OrderItemReturn::REASON_WRONG_ITEM,
                'notes' => 'The listing showed blue but the item received is clearly green in person.',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ]
        );

        $ret = OrderItemReturn::query()->where('order_item_id', $item->id)->firstOrFail();

        $this->actingAs($artisan)
            ->get(route('artisan.returns.show', $ret))
            ->assertOk();

        $otherArtisan = User::factory()->create(['role' => 'artisan', 'status' => 'active']);

        $this->actingAs($otherArtisan)
            ->get(route('artisan.returns.show', $ret))
            ->assertForbidden();
    }
}
