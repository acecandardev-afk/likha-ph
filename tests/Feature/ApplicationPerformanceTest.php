<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Smoke-test latency so regressions (heavy middleware, accidental sync I/O on every request) show up in CI.
     */
    public function test_home_page_responds_within_reasonable_time(): void
    {
        $start = microtime(true);
        $response = $this->get('/');
        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(
            8000,
            $elapsedMs,
            'Home route should respond within 8 seconds (CI-safe ceiling; tighten locally if needed).'
        );
    }

    /**
     * Guard against runaway query counts on the public home route (N+1 in composers, etc.).
     */
    public function test_home_page_query_count_stays_bounded(): void
    {
        DB::enableQueryLog();

        $response = $this->get('/');
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $count = count($queries);

        $this->assertLessThan(
            80,
            $count,
            "Expected fewer than 80 queries for GET /; got {$count}. Inspect composers and eager loads."
        );
    }
}
