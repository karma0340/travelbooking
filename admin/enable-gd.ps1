# Enable GD Extension in XAMPP
# This script will help you enable the GD library

Write-Host "=== XAMPP GD Extension Enabler ===" -ForegroundColor Cyan
Write-Host ""

# Find XAMPP installation
$xamppPath = "C:\xampp"
if (-not (Test-Path $xamppPath)) {
    Write-Host "XAMPP not found at C:\xampp" -ForegroundColor Red
    Write-Host "Please enter your XAMPP installation path:"
    $xamppPath = Read-Host
}

$phpIniPath = "$xamppPath\php\php.ini"

if (-not (Test-Path $phpIniPath)) {
    Write-Host "php.ini not found at $phpIniPath" -ForegroundColor Red
    exit
}

Write-Host "Found php.ini at: $phpIniPath" -ForegroundColor Green
Write-Host ""

# Backup php.ini
$backupPath = "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $phpIniPath $backupPath
Write-Host "Backup created: $backupPath" -ForegroundColor Green
Write-Host ""

# Read php.ini
$content = Get-Content $phpIniPath

# Check if GD is already enabled
$gdEnabled = $false
$modified = $false

for ($i = 0; $i -lt $content.Length; $i++) {
    $line = $content[$i]
    
    # Check for commented GD extension
    if ($line -match '^\s*;extension=gd') {
        Write-Host "Found commented GD extension: $line" -ForegroundColor Yellow
        $content[$i] = $line -replace '^\s*;', ''
        Write-Host "Enabled: $($content[$i])" -ForegroundColor Green
        $modified = $true
        $gdEnabled = $true
    }
    # Check if already enabled
    elseif ($line -match '^\s*extension=gd') {
        Write-Host "GD extension is already enabled!" -ForegroundColor Green
        $gdEnabled = $true
    }
}

if ($modified) {
    # Save modified php.ini
    $content | Set-Content $phpIniPath
    Write-Host ""
    Write-Host "php.ini has been updated!" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANT: You must restart Apache for changes to take effect!" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Steps:" -ForegroundColor Cyan
    Write-Host "1. Open XAMPP Control Panel" -ForegroundColor White
    Write-Host "2. Stop Apache" -ForegroundColor White
    Write-Host "3. Start Apache" -ForegroundColor White
    Write-Host ""
    Write-Host "After restarting, run the diagnostic again:" -ForegroundColor Cyan
    Write-Host "http://localhost/git2/admin/test-image-upload.php" -ForegroundColor White
} elseif ($gdEnabled) {
    Write-Host ""
    Write-Host "GD is already enabled. If it's still not working:" -ForegroundColor Yellow
    Write-Host "1. Restart Apache in XAMPP Control Panel" -ForegroundColor White
    Write-Host "2. Check if php_gd2.dll exists in: $xamppPath\php\ext\" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "Could not find GD extension line in php.ini" -ForegroundColor Red
    Write-Host "Manually add this line to php.ini:" -ForegroundColor Yellow
    Write-Host "extension=gd" -ForegroundColor White
    Write-Host ""
    Write-Host "Then restart Apache" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
