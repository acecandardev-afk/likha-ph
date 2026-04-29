<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPackage;
use App\Models\OrderPackageItem;
use App\Models\Payment;
use App\Models\Rider;
use App\Models\RiderRemittanceReport;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiderCodRemittanceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function riderUserWithProfile(): User
    {
        $user = User::factory()->rider()->create([
            'password' => Hash::make('password'),
        ]);

        Rider::create([
            'rider_id' => 'RDR-REM-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'full_name' => 'Remittance Rider',
            'contact_number' => '09171234567',
            'email' => $user->email,
            'status' => Rider::STATUS_AVAILABLE,
            'date_created' => now(),
        ]);

        return $user;
    }

    public function test_guest_get_cod_remittance_redirects_to_cod_settlement(): void
    {
        $this->get('/rider/cod-remittance')->assertRedirect('/rider/cod-settlement');
    }

    public function test_guest_requesting_cod_settlement_after_redirect_is_sent_to_login(): void
    {
        $this->get('/rider/cod-settlement')->assertRedirect(route('login'));
    }

    public function test_authenticated_rider_get_cod_remittance_redirects_to_cod_settlement(): void
    {
        $user = $this->riderUserWithProfile();

        $this->actingAs($user)->get('/rider/cod-remittance')
            ->assertRedirect('/rider/cod-settlement');
    }

    public function test_cod_delivery_auto_accumulates_daily_remittance_report(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $artisan = User::factory()->create(['role' => 'artisan']);
        $riderUser = $this->riderUserWithProfile();
        $rider = $riderUser->riderProfile;

        $order = Order::create([
            'order_number' => 'ORD-RDR-'.strtoupper(substr(uniqid(), -10)),
            'customer_id' => $customer->id,
            'artisan_id' => $artisan->id,
            'subtotal' => 200,
            'platform_fee' => 10,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total' => 210,
            'status' => 'processing',
            'country' => 'Philippines',
            'delivery_status' => DeliveryService::STATUS_OUT_FOR_DELIVERY,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => 210,
            'verification_status' => 'verified',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Test item',
            'product_description' => null,
            'price' => 100,
            'quantity' => 2,
            'subtotal' => 200,
        ]);

        $pkg = OrderPackage::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'rider_id' => $rider->id,
            'delivery_status' => DeliveryService::STATUS_OUT_FOR_DELIVERY,
            'delivery_assigned_at' => now()->subHour(),
            'delivery_completed_at' => null,
        ]);

        OrderPackageItem::create([
            'order_package_id' => $pkg->id,
            'order_item_id' => $item->id,
            'quantity' => 2,
        ]);

        app(DeliveryService::class)->updateDeliveryStatus($pkg->fresh(), DeliveryService::STATUS_DELIVERED, $riderUser);

        $report = RiderRemittanceReport::query()->where('rider_id', $rider->id)->first();
        $this->assertNotNull($report);

        $this->assertEqualsWithDelta(210.0, (float) $report->cod_declared_total, 0.01);
        $this->assertEqualsWithDelta(190.0, (float) $report->seller_pool_declared, 0.01);
        $this->assertEqualsWithDelta(10.0, (float) $report->platform_pool_declared, 0.01);
        $this->assertNotNull($report->submitted_at);
        $this->assertStringContainsString('automatically', strtolower((string) $report->notes));
    }
}
