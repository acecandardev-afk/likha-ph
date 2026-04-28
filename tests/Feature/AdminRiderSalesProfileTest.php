<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPackage;
use App\Models\OrderPackageItem;
use App\Models\Rider;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRiderSalesProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createDemoRider(): Rider
    {
        $user = User::factory()->rider()->create([
            'email' => 'rider-profile-'.uniqid('', true).'@example.com',
            'password' => Hash::make('password'),
        ]);

        return Rider::create([
            'rider_id' => 'RDR-PROFILE-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'full_name' => 'Profile Test Rider',
            'contact_number' => '09171234567',
            'email' => $user->email,
            'status' => Rider::STATUS_AVAILABLE,
            'date_created' => now(),
        ]);
    }

    public function test_guest_is_redirected_from_admin_rider_profile(): void
    {
        $rider = $this->createDemoRider();

        $response = $this->get(route('admin.riders.show', $rider));

        $response->assertRedirect();
    }

    public function test_non_admin_cannot_view_admin_rider_profile(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rider = $this->createDemoRider();

        $response = $this->actingAs($customer)->get(route('admin.riders.show', $rider));

        $response->assertForbidden();
    }

    public function test_admin_can_view_rider_sales_profile(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-profile-'.uniqid('', true).'@example.com',
        ]);
        $rider = $this->createDemoRider();

        $response = $this->actingAs($admin)->get(route('admin.riders.show', $rider));

        $response->assertOk();
        $response->assertSee('Profile Test Rider');
        $response->assertSee('COD goods delivered');
        $response->assertSee('Delivery history');
    }

    public function test_delivered_package_records_rider_fee_and_shows_on_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['role' => 'customer']);
        $artisan = User::factory()->create(['role' => 'artisan']);

        $order = Order::create([
            'order_number' => 'ORD-PR-'.strtoupper(substr(uniqid(), -10)),
            'customer_id' => $customer->id,
            'artisan_id' => $artisan->id,
            'subtotal' => 200,
            'platform_fee' => 10,
            'total' => 210,
            'status' => 'confirmed',
            'country' => 'Philippines',
            'delivery_status' => DeliveryService::STATUS_DELIVERED,
            'delivery_completed_at' => now(),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Test product',
            'product_description' => null,
            'price' => 100,
            'quantity' => 2,
            'subtotal' => 200,
        ]);

        $riderRecord = $this->createDemoRider();

        $pkg = OrderPackage::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'rider_id' => $riderRecord->id,
            'delivery_status' => DeliveryService::STATUS_DELIVERED,
            'delivery_assigned_at' => now()->subHour(),
            'delivery_completed_at' => now()->subMinute(),
            'platform_fee_share' => 10,
            'platform_fee_realized_at' => now()->subMinute(),
            'rider_fee_amount' => config('fees.rider_fee_per_package', 50),
        ]);

        OrderPackageItem::create([
            'order_package_id' => $pkg->id,
            'order_item_id' => $item->id,
            'quantity' => 2,
        ]);

        $fee = round((float) config('fees.rider_fee_per_package', 50), 2);

        $response = $this->actingAs($admin)->get(route('admin.riders.show', $riderRecord));

        $response->assertOk();
        $response->assertSee(number_format((float) $fee, 2));
        $response->assertSee('200.00');
        $response->assertSee($order->order_number);
    }
}
