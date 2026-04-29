<?php

namespace Tests\Feature;

use App\Models\Rider;
use App\Models\User;
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

    public function test_rider_can_submit_cod_remittance_and_it_is_stored(): void
    {
        $user = $this->riderUserWithProfile();
        $rider = $user->riderProfile;

        $this->actingAs($user)->post(route('rider.cod-remittance.store'), [
            'report_date' => now()->format('Y-m-d'),
            'cod_declared_total' => '1250.50',
        ])->assertRedirect();

        $this->assertDatabaseHas('rider_remittance_reports', [
            'rider_id' => $rider->id,
            'cod_declared_total' => '1250.50',
        ]);
    }

    public function test_future_report_date_is_rejected(): void
    {
        $user = $this->riderUserWithProfile();

        $this->actingAs($user)->post(route('rider.cod-remittance.store'), [
            'report_date' => now()->addDay()->format('Y-m-d'),
            'cod_declared_total' => '100',
        ])->assertSessionHasErrors('report_date');
    }
}
