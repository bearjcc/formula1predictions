# Run discovered Unit/Feature suites in two batches (avoids timeout on Windows).
# Usage: from repo root, .\scripts\test-batches.ps1
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

& php .\scripts\run-test-batches.php --batches=2
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
