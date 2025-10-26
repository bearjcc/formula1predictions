# Pest v4 Features Implementation

This document outlines the new Pest v4 features that have been implemented in the Formula 1 Predictions application.

## ✅ Completed Features

### 1. Browser Testing
- **Location**: `tests/Browser/`
- **Features Implemented**:
  - Real browser testing with Playwright
  - Device-specific testing (desktop, mobile, tablet)
  - Dark/light mode testing
  - JavaScript error detection
  - Console log checking

### 2. Smoke Testing
- **Location**: `tests/Browser/SmokeTest.php`
- **Features Implemented**:
  - `assertNoSmoke()` for comprehensive page testing
  - Multi-page testing with single command
  - Cross-device smoke testing

### 3. Visual Regression Testing
- **Location**: `tests/Browser/VisualRegressionTest.php`
- **Features Implemented**:
  - `assertScreenshotsMatches()` for visual consistency
  - Baseline screenshot comparison
  - Multi-device visual testing

### 4. Conditional Test Skipping
- **Location**: `tests/Browser/ConditionalTests.php`
- **Features Implemented**:
  - `skipLocally()` - Skip tests when running locally
  - `skipOnCi()` - Skip tests when running on CI
  - Multi-browser testing support

### 5. Parallel Testing
- **Location**: `tests/Browser/ParallelBrowserTest.php`
- **Features Implemented**:
  - `->parallel()` modifier for concurrent test execution
  - Faster test suite execution

### 6. New Expectations
- **Location**: `tests/Pest.php` and `tests/Unit/`
- **Features Implemented**:
  - `toBeSlug()` - Validate slug format
  - Suspicious characters detection (enabled by default)

## 🚀 How to Run

### Browser Tests
```bash
# Run all browser tests
php artisan test tests/Browser/

# Run specific browser test
php artisan test tests/Browser/DashboardBrowserTest.php

# Run with parallel execution
php artisan test tests/Browser/ --parallel

# Run with sharding (for CI)
php artisan test tests/Browser/ --shard=1/4 --parallel
```

### Smoke Tests
```bash
# Run smoke tests
php artisan test tests/Browser/SmokeTest.php
```

### Visual Regression Tests
```bash
# Run visual regression tests
php artisan test tests/Browser/VisualRegressionTest.php
```

### Unit Tests with New Expectations
```bash
# Run slug validation tests
php artisan test tests/Unit/SlugValidationTest.php

# Run suspicious characters tests
php artisan test tests/Unit/SuspiciousCharactersTest.php
```

## 📋 Test Categories

### Browser Tests
1. **DashboardBrowserTest.php** - Dashboard functionality testing
2. **PredictionFormBrowserTest.php** - Form interactions and validation
3. **SmokeTest.php** - Application-wide smoke testing
4. **VisualRegressionTest.php** - Visual consistency testing
5. **ConditionalTests.php** - Environment-specific testing
6. **ParallelBrowserTest.php** - Parallel execution testing

### Unit Tests
1. **SlugValidationTest.php** - New `toBeSlug()` expectation
2. **SuspiciousCharactersTest.php** - Code quality validation

## 🔧 Configuration

### Pest Configuration
- Updated `tests/Pest.php` with new `toBeSlug()` expectation
- Browser testing configured with Playwright
- Parallel testing enabled

### Dependencies Added
- `pestphp/pest-plugin-browser` - Browser testing plugin
- `playwright` - Browser automation framework

## 🎯 Key Benefits

1. **Real Browser Testing** - Tests run in actual browsers, not just HTTP requests
2. **Visual Regression** - Catch UI changes automatically
3. **Smoke Testing** - Ensure all pages load without errors
4. **Parallel Execution** - Faster test suites
5. **Device Testing** - Test on multiple device types
6. **Conditional Skipping** - Optimize test execution based on environment

## 📝 Notes

- Browser tests require Playwright to be installed
- Visual regression tests create baseline screenshots on first run
- Parallel tests may require more system resources
- Conditional tests help optimize CI/CD pipelines

## 🔄 Next Steps

- [ ] Add more comprehensive form interaction tests
- [ ] Implement API testing with browser context
- [ ] Add performance testing scenarios
- [ ] Create custom browser test helpers
- [ ] Set up CI/CD pipeline with sharding
