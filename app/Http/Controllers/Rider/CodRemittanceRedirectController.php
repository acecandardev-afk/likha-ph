<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * GET /rider/cod-remittance is not a form page; POST saves the declaration.
 * This redirect avoids 405 errors for bookmarks or direct links without using a
 * route closure (route:cache friendly) and without throttling (avoids cache/Redis failures on thin hosts).
 */
class CodRemittanceRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('rider.cod-settlement');
    }
}
