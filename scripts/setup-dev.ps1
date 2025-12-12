<#
PowerShell helper to bootstrap local development on Windows.
Run as: `.	ools\setup-dev.ps1` from repository root (PowerShell).
#>

Write-Host "Starting Giftos dev setup..."

if (-Not (Test-Path -Path .env)) {
    if (Test-Path -Path .env.example) {
        Copy-Item .env.example .env
        Write-Host "Copied .env.example -> .env"
    } else {
        Write-Host "No .env.example found; please create .env manually." -ForegroundColor Yellow
    }
}

Write-Host "Installing Composer dependencies..."
composer install

Write-Host "Installing NPM dependencies..."
npm install

Write-Host "Building assets (development)..."
npm run dev

Write-Host "Generating app key..."
php artisan key:generate | Out-Null

Write-Host "Running migrations..."
php artisan migrate --seed

Write-Host "Setup completed. Run 'php artisan serve' to start the local server."
