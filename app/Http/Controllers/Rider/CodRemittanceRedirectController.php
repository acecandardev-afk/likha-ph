<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * GET /rider/cod-remittance is not a form page; POST saves the declaration.
 *
 * Uses a plain path redirect (no route() resolution) so cached routes / APP_URL cannot break generation.
 * Registered outside auth+rider middleware so this hop cannot trip rider middleware edge cases on hosts.
 */
class CodRemittanceRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect('/rider/cod-settlement');
    }
}
