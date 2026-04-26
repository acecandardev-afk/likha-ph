<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;

class RiderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'rider']);
    }

    protected function getRiderUser()
    {
        return auth()->user();
    }
}
