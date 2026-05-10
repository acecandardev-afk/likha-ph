<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends Controller
{
    public function index(Request $request): Response
    {
        $token = config('app.health_diagnostic_token');
        if (is_string($token) && $token !== '' && hash_equals($token, (string) $request->query('token', ''))) {
            $manifestPath = public_path('build/manifest.json');

            $payload = [
                'php' => PHP_VERSION,
                'vite_manifest_exists' => is_file($manifestPath),
                'storage_logs_writable' => is_writable(storage_path('logs')),
                'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
                'database_connected' => false,
            ];

            try {
                DB::connection()->getPdo();
                $payload['database_connected'] = true;
            } catch (\Throwable $e) {
                $payload['database_error'] = config('app.debug') ? $e->getMessage() : 'connection failed (enable APP_DEBUG temporarily or read storage/logs/laravel.log)';
            }

            return response()->json($payload, 200, [], JSON_PRETTY_PRINT);
        }

        return response('OK', 200);
    }
}
