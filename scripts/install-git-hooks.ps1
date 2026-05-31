# Point this repo at .githooks/ (pre-commit, commit-msg). Run from repo root.
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

git config core.hooksPath .githooks
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "core.hooksPath set to .githooks"
Write-Host "Hooks: pre-commit (size + Pint), commit-msg (Conventional Commits)"
Write-Host "Stats: .\scripts\git\change-stats.ps1"
Write-Host "Skip once: `$env:GIT_HYGIENE_SKIP='1'"
