<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthController extends Controller
{
    public function index(): Response
    {
        return response('OK', 200);
    }
}
