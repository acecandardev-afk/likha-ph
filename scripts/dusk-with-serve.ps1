<# 
  One-shot Laravel Dusk runner for Windows: migrates the Dusk DB, starts
  `php artisan serve` with APP_ENV=dusk, runs `php artisan dusk`, stops the server.

  Prerequisites: Google Chrome installed; `composer install` done; `php artisan dusk:chrome-driver` run once.
#>
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

if (-not (Test-Path "database\dusk.sqlite")) {
    New-Item -ItemType File -Path "database\dusk.sqlite" | Out-Null
}

Write-Host "Migrating Dusk database..."
& php artisan migrate:fresh --env=dusk --force --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Aligning ChromeDriver to installed Chrome (if needed)..."
& php artisan dusk:chrome-driver --detect 2>$null

Write-Host "Starting dev server (APP_ENV=dusk) on http://127.0.0.1:8000 ..."
$serve = Start-Process -FilePath "php" -ArgumentList @("artisan","serve","--host=127.0.0.1","--port=8000","--env=dusk") -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 3

try {
    & php artisan dusk
    exit $LASTEXITCODE
}
finally {
    if ($serve -and -not $serve.HasExited) {
        Stop-Process -Id $serve.Id -Force -ErrorAction SilentlyContinue
    }
}
