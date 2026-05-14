<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourismAndMonthlyReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function riderWithProfile(): User
    {
        $user = User::factory()->rider()->create();

        Rider::create([
            'rider_id' => 'RDR-TST-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'full_name' => 'Test Rider',
            'contact_number' => '09171234567',
            'email' => $user->email,
            'status' => Rider::STATUS_AVAILABLE,
            'date_created' => now(),
        ]);

        return $user;
    }

    public function test_home_shows_tourism_storefront_copy(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Discover Guihulngan', false)
            ->assertSee('home-storefront--tourism', false);
    }

    public function test_products_and_artisans_indexes_respond(): void
    {
        $this->get(route('products.index'))->assertOk();
        $this->get(route('artisans.index'))->assertOk();
    }

    public function test_guest_redirected_from_monthly_reports(): void
    {
        $this->get(route('admin.reports.monthly'))->assertRedirect(route('login'));
        $this->get(route('artisan.reports.monthly'))->assertRedirect(route('login'));
        $this->get(route('rider.reports.monthly'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_open_other_roles_monthly_reports(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('admin.reports.monthly'))->assertForbidden();
        $this->actingAs($customer)->get(route('artisan.reports.monthly'))->assertForbidden();
        $this->actingAs($customer)->get(route('rider.reports.monthly'))->assertForbidden();
    }

    public function test_admin_monthly_report_renders_print_shell_and_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.monthly'));

        $response->assertOk();
        $response->assertSee('report-print-root', false);
        $response->assertSee('no-print', false);
        $response->assertSee('window.print()', false);
        $response->assertSee('Monthly report', false);
        $response->assertSee('GMV', false);
    }

    public function test_admin_monthly_report_accepts_year_and_month_query(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.monthly', [
            'year' => 2024,
            'month' => 6,
        ]));

        $response->assertOk();
        $response->assertSee('June 2024', false);
    }

    public function test_artisan_monthly_report_includes_workshop_line_and_print(): void
    {
        $artisan = User::factory()->create([
            'role' => 'artisan',
            'status' => 'active',
            'name' => 'Artisan Monthly Test',
        ]);

        $response = $this->actingAs($artisan)->get(route('artisan.reports.monthly'));

        $response->assertOk();
        $response->assertSee('report-print-root', false);
        $response->assertSee('Artisan Monthly Test', false);
        $response->assertSee('window.print()', false);
    }

    public function test_rider_monthly_report_ok_when_profile_exists(): void
    {
        $riderUser = $this->riderWithProfile();

        $response = $this->actingAs($riderUser)->get(route('rider.reports.monthly'));

        $response->assertOk();
        $response->assertSee('report-print-root', false);
        $response->assertSee('Test Rider', false);
        $response->assertSee('window.print()', false);
    }

    public function test_rider_monthly_report_forbidden_without_rider_profile(): void
    {
        $riderUser = User::factory()->rider()->create();

        $this->actingAs($riderUser)
            ->get(route('rider.reports.monthly'))
            ->assertForbidden();
    }

    public function test_tourism_theme_stylesheet_defines_print_rules(): void
    {
        $scss = file_get_contents(resource_path('sass/pages/_tourism-theme.scss'));

        $this->assertStringContainsString('.report-print-root', $scss);
        $this->assertStringContainsString('@media print', $scss);
        $this->assertStringContainsString('.no-print', $scss);
    }

    public function test_cancelled_order_disappears_from_customer_order_list_after_24_hours(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Carbon::setTestNow(Carbon::parse('2026-03-10 12:00:00'));

        $customer = User::factory()->create(['role' => 'customer']);
        $artisan = User::factory()->create(['role' => 'artisan', 'status' => 'active']);

        $category = Category::create([
            'name' => 'Souvenirs',
            'description' => null,
        ]);

        $product = Product::create([
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'name' => 'Test craft',
            'description' => 'd',
            'price' => 50,
            'stock' => 20,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $orderNumber = 'ORD-T24-'.strtoupper(substr(uniqid(), -10));

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'artisan_id' => $artisan->id,
            'subtotal' => 100,
            'platform_fee' => 0,
            'total' => 100,
            'status' => 'pending',
            'country' => 'Philippines',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_description' => null,
            'price' => 50,
            'quantity' => 2,
            'subtotal' => 100,
        ]);

        $this->actingAs($customer)
            ->patch(route('customer.orders.cancel', $order))
            ->assertRedirect(route('customer.orders.index'));

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(\App\Services\DeliveryService::STATUS_CANCELLED, $order->delivery_status);

        $list = $this->actingAs($customer)->get(route('customer.orders.index'));
        $list->assertOk();
        $list->assertSee('disappear from this list 24 hours', false);
        $list->assertSee($orderNumber, false);

        Carbon::setTestNow(Carbon::parse('2026-03-11 13:00:00'));

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertDontSee($orderNumber, false);
    }
}
