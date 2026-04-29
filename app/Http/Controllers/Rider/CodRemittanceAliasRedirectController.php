<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * GET bookmark / typo handler for POST-only COD remittance URL.
 * Uses a plain RedirectResponse (no route():route(), no Laravel RedirectController) for maximum hosting compatibility.
 */
final class CodRemittanceAliasRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return new RedirectResponse('/rider/cod-settlement', 302);
    }
}
