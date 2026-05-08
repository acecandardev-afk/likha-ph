<?php

namespace Tests\Browser;

use App\Models\Rider;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MonthlyReportPrintTest extends DuskTestCase
{
    public function test_tourism_home_and_role_monthly_reports_expose_print_markup(): void
    {
        $suffix = str_replace('.', '', uniqid('d', true));

        $admin = User::factory()->admin()->create([
            'email' => "dusk-admin-{$suffix}@example.test",
        ]);

        $artisan = User::factory()->create([
            'role' => 'artisan',
            'status' => 'active',
            'email' => "dusk-artisan-{$suffix}@example.test",
        ]);

        $riderUser = User::factory()->rider()->create([
            'email' => "dusk-rider-{$suffix}@example.test",
        ]);

        Rider::create([
            'rider_id' => 'RDR-DUSK-'.strtoupper(substr($suffix, 0, 8)),
            'user_id' => $riderUser->id,
            'full_name' => 'Dusk Rider Report',
            'contact_number' => '09170000001',
            'email' => $riderUser->email,
            'status' => Rider::STATUS_AVAILABLE,
            'date_created' => now(),
        ]);

        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->waitForText('Guihulngan Tourism', 10);
        });

        $this->browse(function (Browser $browser) use ($admin): void {
            $browser->loginAs($admin)
                ->visit(route('admin.reports.monthly'))
                ->waitForText('Monthly report', 15)
                ->assertSee('Print')
                ->assertSourceHas('report-print-root')
                ->assertSourceHas('no-print')
                ->assertSourceHas('window.print');
        });

        $this->browse(function (Browser $browser) use ($artisan): void {
            $browser->loginAs($artisan)
                ->visit(route('artisan.reports.monthly'))
                ->waitForText('Monthly report', 15)
                ->assertSee('Print')
                ->assertSourceHas('report-print-root')
                ->assertSourceHas('window.print');
        });

        $this->browse(function (Browser $browser) use ($riderUser): void {
            $browser->loginAs($riderUser)
                ->visit(route('rider.reports.monthly'))
                ->waitForText('Monthly report', 15)
                ->assertSee('Print')
                ->assertSourceHas('Dusk Rider Report')
                ->assertSourceHas('report-print-root')
                ->assertSourceHas('window.print');
        });
    }
}
