# Print working-tree and staged diff stats (for agents and humans).
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

# Git may write CRLF warnings to stderr; do not treat as terminating errors.
$prevEap = $ErrorActionPreference
$ErrorActionPreference = "SilentlyContinue"

$branch = (git branch --show-current 2>$null)
if (-not $branch) { $branch = "(detached)" }
Write-Host "branch: $branch"

if ($branch -in @("main", "master")) {
    Write-Warning "editing on protected default branch; use a feature branch or worktree."
}

function Get-StagedStats {
    $names = @(git diff --cached --name-only 2>$null | Where-Object { $_ })
    $lines = 0
    if ($names.Count -gt 0) {
        foreach ($row in (git diff --cached --numstat 2>$null)) {
            if ($row -match '^(\d+|-)\s+(\d+|-)\s+') {
                $a = if ($Matches[1] -eq '-') { 0 } else { [int]$Matches[1] }
                $d = if ($Matches[2] -eq '-') { 0 } else { [int]$Matches[2] }
                $lines += $a + $d
            }
        }
    }
    return @{ Files = $names.Count; Lines = $lines }
}

function Get-UnstagedStats {
    $names = @(git diff --name-only 2>$null | Where-Object { $_ })
    $lines = 0
    if ($names.Count -gt 0) {
        foreach ($row in (git diff --numstat 2>$null)) {
            if ($row -match '^(\d+|-)\s+(\d+|-)\s+') {
                $a = if ($Matches[1] -eq '-') { 0 } else { [int]$Matches[1] }
                $d = if ($Matches[2] -eq '-') { 0 } else { [int]$Matches[2] }
                $lines += $a + $d
            }
        }
    }
    return @{ Files = $names.Count; Lines = $lines }
}

$staged = Get-StagedStats
$unstaged = Get-UnstagedStats

$ErrorActionPreference = $prevEap

Write-Host "staged:   $($staged.Files) files, $($staged.Lines) lines"
Write-Host "unstaged: $($unstaged.Files) files, $($unstaged.Lines) lines"
Write-Host ""
$ErrorActionPreference = "SilentlyContinue"
git diff --stat 2>$null | Select-Object -Last 5
$ErrorActionPreference = $prevEap
