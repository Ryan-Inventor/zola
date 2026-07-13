# INF-04 - Migrations + seed initial (preparation AUTH-01)
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot

Write-Host "==> Zola - migrate + seed" -ForegroundColor Cyan

& php "$Root\scripts\ensure-database.php"

Push-Location "$Root\zola-api"
try {
    php artisan migrate --force
    php artisan db:seed --force
    Write-Host "==> Seed termine." -ForegroundColor Green
}
finally {
    Pop-Location
}
