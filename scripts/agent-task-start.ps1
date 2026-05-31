# Create a git worktree + agent branch for isolated AI work.
# Usage: .\scripts\agent-task-start.ps1 -Task "f1-123-scoring-fix" [-Base main]
param(
    [Parameter(Mandatory = $true)]
    [string]$Task,

    [string]$Base = "main"
)

$ErrorActionPreference = "Stop"
$repoRoot = (git -C (Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)) rev-parse --show-toplevel)
if (-not $repoRoot) { throw "Not inside a git repository." }

$slug = ($Task -replace '[^a-zA-Z0-9]+', '-').Trim('-').ToLower()
if (-not $slug) { throw "Task name must contain at least one alphanumeric character." }

$branch = "agent/$slug"
$repoName = Split-Path -Leaf $repoRoot
$parent = Split-Path -Parent $repoRoot
$worktree = Join-Path $parent "$repoName-wt-$slug"

if (Test-Path $worktree) {
    throw "Worktree path already exists: $worktree"
}

Push-Location $repoRoot
try {
    $baseRef = $Base
    git rev-parse --verify "origin/$Base" 2>$null | Out-Null
    if ($LASTEXITCODE -eq 0) { $baseRef = "origin/$Base" }

    git worktree add -b $branch $worktree $baseRef
    if ($LASTEXITCODE -ne 0) {
        git worktree add -b $branch $worktree $Base
    }
    if ($LASTEXITCODE -ne 0) { throw "git worktree add failed." }
}
finally {
    Pop-Location
}

Write-Host ""
Write-Host "Branch:   $branch"
Write-Host "Worktree: $worktree"
Write-Host ""
Write-Host "Next:"
Write-Host "  1. Open the worktree folder in Cursor (not the main checkout)."
Write-Host "  2. Run: .\scripts\install-git-hooks.ps1"
Write-Host "  3. After edits: .\scripts\git\change-stats.ps1 before each commit."
