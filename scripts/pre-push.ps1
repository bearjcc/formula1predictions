# Pre-push checks: audits, readiness, style, tests, build. Run from repo root: .\scripts\pre-push.ps1
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

Write-Host "=== Composer audit ==="
& composer audit
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "=== Readiness check ==="
& php artisan app:check-ready
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "=== Pint (dirty files) ==="
& vendor/bin/pint --dirty --test
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "=== Tests (two batches) ==="
& .\scripts\test-batches.ps1
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "=== Frontend build ==="
& npm run build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "=== NPM audit (high/critical; optional) ==="
& npm audit --audit-level=high
if ($LASTEXITCODE -ne 0) { Write-Host "(NPM audit reported issues; fix when convenient)" }

Write-Host "Pre-push checks passed."
