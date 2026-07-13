# INF-04 - Demarre l'environnement de dev local (MySQL + API + Frontend) en une commande
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot

function Stop-PortListener([int]$Port) {
    Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue |
        ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }
}

function Wait-ForAnyHttp([string[]]$Urls, [int]$MaxSeconds = 90) {
    $deadline = (Get-Date).AddSeconds($MaxSeconds)
    while ((Get-Date) -lt $deadline) {
        foreach ($url in $Urls) {
            $code = & curl.exe -s -o NUL -w "%{http_code}" $url 2>$null
            if ($code -match '^[1-5]\d{2}$') {
                return $true
            }
        }
        Start-Sleep -Seconds 2
    }
    return $false
}

Write-Host "==> Zola - environnement de developpement" -ForegroundColor Cyan

& "$PSScriptRoot\seed.ps1"

$apiPort = 8000
$webPort = 3000

Stop-PortListener $apiPort
Stop-PortListener $webPort
Start-Sleep -Seconds 1

Write-Host "==> Demarrage API (port $apiPort)..." -ForegroundColor Yellow
Start-Process -FilePath "php" -ArgumentList @(
    "artisan", "serve", "--host=127.0.0.1", "--port=$apiPort"
) -WorkingDirectory "$Root\zola-api" -WindowStyle Minimized

Write-Host "==> Demarrage Frontend (port $webPort)..." -ForegroundColor Yellow
Start-Process -FilePath "npm.cmd" -ArgumentList @("run", "dev") `
    -WorkingDirectory "$Root\zola-web" -WindowStyle Minimized

Write-Host "==> Attente des services..." -ForegroundColor Yellow

$apiOk = Wait-ForAnyHttp @(
    "http://127.0.0.1:$apiPort/api/v1/test",
    "http://localhost:$apiPort/api/v1/test"
)
$webOk = Wait-ForAnyHttp @(
    "http://127.0.0.1:$webPort/",
    "http://localhost:$webPort/"
)

if ($apiOk -and $webOk) {
    Write-Host ""
    Write-Host "[OK] Environnement Zola operationnel" -ForegroundColor Green
    Write-Host "   API : http://localhost:$apiPort/api/v1"
    Write-Host "   Web : http://localhost:$webPort"
    exit 0
}

Write-Host ""
$msg = "[ERREUR] Echec du demarrage - API=" + $apiOk + " Web=" + $webOk
Write-Host $msg -ForegroundColor Red
exit 1
