<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function sellerAgreement(): View
    {
        return view('legal.seller-agreement');
    }
}
