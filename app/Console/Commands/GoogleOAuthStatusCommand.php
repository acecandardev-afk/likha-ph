<?php

namespace App\Console\Commands;

use App\Support\GoogleOAuth;
use Illuminate\Console\Command;

class GoogleOAuthStatusCommand extends Command
{
    protected $signature = 'google-oauth:status';

    protected $description = 'Show whether Google OAuth credentials are loaded (values masked)';

    public function handle(): int
    {
        $cfgId = config('services.google.client_id');
        $cfgSecret = config('services.google.client_secret');

        $resolvedId = GoogleOAuth::resolvedClientId();
        $resolvedSecret = GoogleOAuth::resolvedClientSecret();

        $this->line('From config cache / services.php (may be empty if .env changed after config:cache):');
        $this->line('  GOOGLE_CLIENT_ID     '.$this->mask($cfgId));
        $this->line('  GOOGLE_CLIENT_SECRET '.$this->mask($cfgSecret));
        $this->newLine();
        $this->line('Resolved for this app (includes getenv / $_ENV fallback):');
        $this->line('  client_id     '.$this->mask($resolvedId));
        $this->line('  client_secret '.$this->mask($resolvedSecret));
        $this->newLine();
        $this->line('Process environment (getenv):');
        $this->line('  GOOGLE_CLIENT_ID     '.$this->presenceLabel('GOOGLE_CLIENT_ID'));
        $this->line('  GOOGLE_CLIENT_SECRET '.$this->presenceLabel('GOOGLE_CLIENT_SECRET'));
        $this->newLine();

        if (GoogleOAuth::isConfigured()) {
            $this->info('Status: OK — Google sign-in should appear on the site.');

            return self::SUCCESS;
        }

        $this->warn('Status: NOT configured — the sign-in panel will show the setup notice.');
        $this->line('Fix: On this server, edit .env and set both variables (no spaces around =).');
        $this->line('Then run: php artisan config:clear');
        $this->line('If you use config caching in production: php artisan config:cache');

        return self::FAILURE;
    }

    private function mask(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '(empty)';
        }

        $v = trim($value);
        if (strlen($v) <= 10) {
            return substr($v, 0, 3).'…'.substr($v, -2);
        }

        return substr($v, 0, 8).'…'.substr($v, -4);
    }

    private function presenceLabel(string $key): string
    {
        $v = getenv($key);

        return is_string($v) && trim($v) !== '' ? '(set)' : '(empty)';
    }
}
