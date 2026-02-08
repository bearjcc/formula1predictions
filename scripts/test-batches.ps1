# Run full test suite in two batches (avoids timeout on Windows).
# Usage: from repo root, .\scripts\test-batches.ps1
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

$batch1 = @(
    "tests/Unit",
    "tests/Feature/AccessibilityTest.php",
    "tests/Feature/AdminControllerTest.php",
    "tests/Feature/Auth",
    "tests/Feature/AutoLockPredictionsTest.php",
    "tests/Feature/BasicPhase1Test.php",
    "tests/Feature/BotPredictionsSeederTest.php",
    "tests/Feature/BotsSeedCommandTest.php",
    "tests/Feature/ChampionshipOrderBotSeederTest.php",
    "tests/Feature/ChartDataServiceTest.php",
    "tests/Feature/ConsoleCommandsTest.php",
    "tests/Feature/DashboardTest.php",
    "tests/Feature/DataVisualizationTest.php",
    "tests/Feature/DraggableDriverListTest.php",
    "tests/Feature/DraggableTeamListTest.php",
    "tests/Feature/F1ApiTest.php",
    "tests/Feature/FakerBasicSeederTest.php",
    "tests/Feature/FormValidationTest.php"
)
$batch2 = @(
    "tests/Feature/HistoricalDataImportTest.php",
    "tests/Feature/HomePageTest.php",
    "tests/Feature/LeaderboardTest.php",
    "tests/Feature/LivewirePredictionFormTest.php",
    "tests/Feature/MobileOptimizationTest.php",
    "tests/Feature/ModelRelationshipsTest.php",
    "tests/Feature/NotificationTest.php",
    "tests/Feature/PredictionControllerTest.php",
    "tests/Feature/PredictionFormValidationTest.php",
    "tests/Feature/RacesPageTest.php",
    "tests/Feature/RandomBotSeederTest.php",
    "tests/Feature/RealTimeNotificationTest.php",
    "tests/Feature/RoutesTest.php",
    "tests/Feature/ScoreRacePredictionsJobTest.php",
    "tests/Feature/ScoringServiceTest.php",
    "tests/Feature/SimpleHistoricalDataTest.php",
    "tests/Feature/TestUserSeederTest.php",
    "tests/Feature/ViewsTest.php",
    "tests/Feature/WebsiteNavigationTest.php",
    "tests/Feature/AuthorizationTest.php",
    "tests/Feature/Settings"
)

Write-Host "Batch 1 (Unit + Feature A-F)..."
& php artisan test @batch1 2>&1 | Tee-Object -Variable r1 | Write-Host
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Write-Host "Batch 2 (Feature G-Z)..."
& php artisan test @batch2 2>&1 | Tee-Object -Variable r2 | Write-Host
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Write-Host "All batches passed."
