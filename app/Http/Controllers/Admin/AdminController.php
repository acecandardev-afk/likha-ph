<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Get common view data for admin panel.
     */
    protected function getCommonData(): array
    {
        return [
            'admin' => auth()->user(),
        ];
    }
}