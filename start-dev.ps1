# Lance OCD Live en local (Windows PowerShell).
# Usage : powershell -ExecutionPolicy Bypass -File .\start-dev.ps1

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

# PHP 8.4 portable (le PHP 8.1 de XAMPP est trop ancien pour Laravel 13)
$env:Path = "D:\dev\tools\php84;" + $env:Path

# MariaDB de XAMPP
if (-not (Get-Process mysqld -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath "C:\xampp1\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp1\mysql\bin\my.ini", "--standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 4
}

php artisan migrate --force

# Vite (rechargement à chaud) dans une fenêtre séparée, puis le serveur Laravel
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PSScriptRoot'; npm run dev"
Write-Host "Site : http://localhost:8000   Administration : http://localhost:8000/connexion" -ForegroundColor Yellow
php artisan serve --port=8000
