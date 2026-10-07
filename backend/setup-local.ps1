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

# Something already answering on a port (e.g. this site still running in another window) means it is taken.
function PortBusy($p) {
    $c = New-Object System.Net.Sockets.TcpClient
    try { $c.Connect('127.0.0.1', $p); return $true } catch { } finally { $c.Close() }
    try { $l = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback, $p); $l.Start(); $l.Stop(); return $false } catch { return $true }
}

Step 'Preparing (cached settings, routes and templates make every page much faster)'
Run 'php' @('artisan', 'optimize')

# PHP's code cache (OPcache) is usually off for the command line on Windows; without it every request re-reads
# hundreds of files. Turn it on for this server.
$phpFlags = @('-d', 'opcache.enable_cli=1', '-d', 'opcache.validate_timestamps=1', '-d', 'opcache.revalidate_freq=0', '-d', 'opcache.memory_consumption=256', '-d', 'opcache.max_accelerated_files=20000', '-d', 'realpath_cache_size=4096K')
if ((php -r "echo extension_loaded('Zend OPcache') ? 1 : 0;") -ne '1') { $phpFlags = @('-d', 'zend_extension=opcache') + $phpFlags }

Write-Host 'Keep this window open while you use it. Close it (or press Ctrl+C) to stop.' -ForegroundColor Yellow
Write-Host 'The first page after starting can take a few seconds while PHP warms up; after that pages are quick.' -ForegroundColor Yellow
Set-Location public
# PHP's own web server, started directly (php artisan serve fails on some Windows setups). If a port cannot be
# used, the next one is tried.
for ($port = 8000; $port -lt 8020; $port++) {
    if (PortBusy $port) { Write-Host "Port $port is in use, trying $($port + 1)..." -ForegroundColor DarkGray; continue }
    $url = "http://127.0.0.1:$port"
    Step "Starting. Admin panel: $url/admin   Website: $url"
    Start-Job -ArgumentList "$url/admin" { param($u) Start-Sleep -Seconds 3; Start-Process $u } | Out-Null   # opens the browser once the server is up
    $started = Get-Date
    php @phpFlags -S "127.0.0.1:$port" ..\server.php
    # A server that stops within a few seconds never started: try the next port. Otherwise it was stopped on purpose.
    if (((Get-Date) - $started).TotalSeconds -gt 5) { exit 0 }
    Get-Job | Remove-Job -Force
    Write-Host "Could not start on port $port, trying $($port + 1)..." -ForegroundColor DarkGray
}
Write-Host 'No free port found between 8000 and 8019. Restart the computer and try again.' -ForegroundColor Red
