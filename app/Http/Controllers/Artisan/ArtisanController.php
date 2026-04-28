<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;

class ArtisanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'artisan']);
    }

    /**
     * Get authenticated artisan.
     */
    protected function getArtisan()
    {
        return auth()->user();
    }

    /**
     * Get common view data for artisan panel.
     */
    protected function getCommonData(): array
    {
        return [
            'artisan' => $this->getArtisan(),
        ];
    }
}
