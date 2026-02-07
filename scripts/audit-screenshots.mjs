/**
 * Captures screenshots of audit-report features for Formula1Predictions.
 * Run: node scripts/audit-screenshots.mjs
 * Requires: app running at BASE_URL (default http://formula1predictions.test).
 * Optional: ADMIN_EMAIL and ADMIN_PASSWORD env vars for admin screenshots.
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import { join } from 'path';

const BASE_URL = process.env.BASE_URL || 'http://formula1predictions.test';
const OUT_DIR = process.env.SCREENSHOT_DIR || join(process.cwd(), 'screenshots');
const USER_EMAIL = process.env.USER_EMAIL || 'test@example.com';
const USER_PASSWORD = process.env.USER_PASSWORD || 'password';
const ADMIN_EMAIL = process.env.ADMIN_EMAIL;
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD;

const year = '2025';

const publicPages = [
  { name: '01-home', url: '/' },
  { name: '02-auth-login', url: '/login' },
  { name: '03-auth-register', url: '/register' },
  { name: '04-auth-forgot-password', url: '/forgot-password' },
  { name: '05-races-list', url: `/${year}/races` },
  { name: '06-standings', url: `/${year}/standings` },
  { name: '07-standings-drivers', url: `/${year}/standings/drivers` },
  { name: '08-standings-teams', url: `/${year}/standings/teams` },
  { name: '09-standings-predictions', url: `/${year}/standings/predictions` },
  { name: '10-leaderboard', url: '/leaderboard' },
  { name: '11-leaderboard-compare', url: '/leaderboard/compare' },
];

const authPages = [
  { name: '12-dashboard', url: '/dashboard' },
  { name: '13-predict-create', url: '/predict/create' },
  { name: '14-analytics', url: '/analytics' },
  { name: '15-settings-profile', url: '/settings/profile' },
  { name: '16-settings-password', url: '/settings/password' },
  { name: '17-settings-appearance', url: '/settings/appearance' },
  { name: '18-notifications', url: '/notifications' },
  { name: '19-leaderboard-season', url: '/leaderboard/season/' + year },
  { name: '19b-leaderboard-race', url: '/leaderboard/race/' + year + '/1' },
  { name: '19c-leaderboard-user-stats', url: '/leaderboard/user/1' },
];

const adminPages = [
  { name: '20-admin-dashboard', url: '/admin/dashboard' },
  { name: '21-admin-users', url: '/admin/users' },
  { name: '22-admin-predictions', url: '/admin/predictions' },
  { name: '23-admin-races', url: '/admin/races' },
  { name: '24-admin-scoring', url: '/admin/scoring' },
  { name: '25-admin-settings', url: '/admin/settings' },
];

async function takeScreenshot(page, slug, fullPage = true) {
  const path = join(OUT_DIR, `${slug}.png`);
  await page.screenshot({ path, fullPage });
  console.log('  ' + slug + '.png');
}

async function login(page, email, password) {
  await page.goto(new URL('/login', BASE_URL).toString(), { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1000);
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill(password);
  await page.getByRole('button', { name: /log in/i }).click();
  await page.waitForURL(/dashboard|\/settings|\/predict/, { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(500);
}

async function main() {
  await mkdir(OUT_DIR, { recursive: true });
  console.log('Screenshots -> ' + OUT_DIR);
  console.log('Base URL: ' + BASE_URL);

  const browser = await chromium.launch({ headless: true });

  try {
    const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
    const page = await context.newPage();

    for (const { name, url } of publicPages) {
      const full = new URL(url, BASE_URL).toString();
      await page.goto(full, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await page.waitForTimeout(800);
      await takeScreenshot(page, name);
    }

    await login(page, USER_EMAIL, USER_PASSWORD);

    for (const { name, url } of authPages) {
      const full = new URL(url, BASE_URL).toString();
      await page.goto(full, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await page.waitForTimeout(800);
      await takeScreenshot(page, name);
    }

    if (ADMIN_EMAIL && ADMIN_PASSWORD) {
      await login(page, ADMIN_EMAIL, ADMIN_PASSWORD);
      for (const { name, url } of adminPages) {
        const full = new URL(url, BASE_URL).toString();
        await page.goto(full, { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(800);
        await takeScreenshot(page, name);
      }
    } else {
      console.log('(Skip admin screenshots: set ADMIN_EMAIL and ADMIN_PASSWORD to capture)');
    }

    await context.close();
  } finally {
    await browser.close();
  }

  console.log('Done.');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
