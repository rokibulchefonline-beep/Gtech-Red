# Runs the GTech admin panel and website on this Windows computer (for trying it out; not for real customers).
# First time: sets everything up and asks for your admin email and password. Next times: just starts the site.
# Needs PHP and Composer (install Laravel Herd for Windows from herd.laravel.com).
$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

function Step($text) { Write-Host "`n== $text" -ForegroundColor Cyan }
function Run($exe, $argList) {
    & $exe @argList
    if ($LASTEXITCODE -ne 0) { Write-Host "`nThat step failed. Copy the red text above and send it for help." -ForegroundColor Red; Read-Host 'Press Enter to close'; exit 1 }
}

foreach ($tool in 'php', 'composer') {
    if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) {
        Write-Host "$tool was not found. Install Laravel Herd for Windows (herd.laravel.com), open it once, then run this again." -ForegroundColor Red
        Read-Host 'Press Enter to close'; exit 1
    }
}

$firstRun = -not (Test-Path '.env')

if (-not (Test-Path 'vendor\autoload.php')) {
    Step 'Installing the parts the app needs (takes a few minutes the first time)'
    Run 'composer' @('install', '--no-interaction')
}

if ($firstRun) {
    Step 'Creating the settings file (.env) with a simple file database (SQLite)'
    $envText = Get-Content '.env.example' -Raw
    $envText = $envText -replace '(?m)^DB_CONNECTION=.*$', 'DB_CONNECTION=sqlite'
    $envText = $envText -replace '(?m)^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=.*\r?\n', ''
    $envText = $envText -replace '(?m)^APP_URL=.*$', 'APP_URL=http://127.0.0.1:8000'
    Set-Content '.env' $envText -NoNewline
    Run 'php' @('artisan', 'key:generate', '--force')
}

if (-not (Test-Path 'database\database.sqlite')) { New-Item 'database\database.sqlite' -ItemType File | Out-Null }

Step 'Updating the database'
Run 'php' @('artisan', 'migrate', '--force')

if ($firstRun) {
    Step 'Loading the website content'
    if (-not (Test-Path 'public\storage')) { Run 'php' @('artisan', 'storage:link') }
    Run 'php' @('artisan', 'gtech:seed-content')
    Step 'Create your admin login (the password is hidden while you type it)'
    Run 'php' @('artisan', 'gtech:create-admin')
}

Step 'Starting. Admin panel: http://127.0.0.1:8000/admin   Website: http://127.0.0.1:8000'
Write-Host 'Keep this window open while you use it. Close it (or press Ctrl+C) to stop.' -ForegroundColor Yellow
Start-Job { Start-Sleep -Seconds 3; Start-Process 'http://127.0.0.1:8000/admin' } | Out-Null   # opens the browser once the server is up
php artisan serve --port=8000
