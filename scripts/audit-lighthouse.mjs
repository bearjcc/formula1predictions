/**
 * Runs Lighthouse mobile audit for Formula1Predictions.
 * Requires: app running at BASE_URL (default http://formula1predictions.test).
 * Run: npm run audit:lighthouse
 * Env: BASE_URL or APP_URL to override; LIGHTHOUSE_OUT for report path.
 */
import { execSync } from 'child_process';
import { join } from 'path';

const baseUrl = process.env.BASE_URL || process.env.APP_URL || 'http://formula1predictions.test';
const outPath = process.env.LIGHTHOUSE_OUT || join(process.cwd(), 'lighthouse-mobile.html');

const cmd = `npx lighthouse "${baseUrl}" --mobile --output=html --output-path="${outPath}" --chrome-flags="--headless"`;

console.log('Running Lighthouse mobile audit for', baseUrl);
execSync(cmd, { stdio: 'inherit', shell: true });
console.log('Report written to', outPath);
