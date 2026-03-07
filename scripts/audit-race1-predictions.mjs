/**
 * Audit race 1 predictions on production DB: driver_order, fastest_lap, dnf_predictions.
 * Requires PRODUCTION_DATABASE_URL in env (e.g. mysql://user:pass@host:port/db).
 *
 * Run: node scripts/audit-race1-predictions.mjs
 * Or:  PRODUCTION_DATABASE_URL="mysql://..." node scripts/audit-race1-predictions.mjs
 */

import mysql from 'mysql2/promise';

const url = process.env.PRODUCTION_DATABASE_URL;
if (!url || !url.startsWith('mysql://')) {
  console.error('Set PRODUCTION_DATABASE_URL (e.g. mysql://user:pass@host:port/db)');
  process.exit(1);
}

// Parse URL (simple: no auth encoding handling)
const u = new URL(url);
const config = {
  host: u.hostname,
  port: parseInt(u.port || '3306', 10),
  user: u.username || 'root',
  password: u.password || '',
  database: u.pathname.slice(1) || 'railway',
};

const conn = await mysql.createConnection(config);
const [rows] = await conn.execute(
  `SELECT
    p.id,
    p.user_id,
    COALESCE(u.name, u.email, CONCAT('user#', p.user_id)) AS user_display,
    p.season,
    p.status,
    JSON_LENGTH(COALESCE(JSON_EXTRACT(p.prediction_data, '$.driver_order'), '[]')) AS driver_order_count,
    CASE
      WHEN JSON_UNQUOTE(JSON_EXTRACT(p.prediction_data, '$.fastest_lap')) IS NULL THEN 'no'
      WHEN JSON_UNQUOTE(JSON_EXTRACT(p.prediction_data, '$.fastest_lap')) = '' THEN 'no'
      ELSE 'yes'
    END AS fastest_lap_set,
    JSON_LENGTH(COALESCE(JSON_EXTRACT(p.prediction_data, '$.dnf_predictions'), '[]')) AS dnf_count
  FROM predictions p
  LEFT JOIN users u ON u.id = p.user_id
  WHERE p.type = 'race' AND p.race_round = 1
  ORDER BY p.season, p.user_id`
);
await conn.end();

if (rows.length === 0) {
  console.log('No race 1 predictions found.');
  process.exit(0);
}

console.log('Race 1 predictions:', rows.length);
console.log('');
const headers = ['id', 'season', 'user', 'status', 'driver_order_count', 'fastest_lap_set', 'dnf_count'];
const colWidths = [6, 8, 24, 12, 18, 16, 10];
const line = colWidths.map((w) => '-'.repeat(w)).join(' ');
console.log(headers.map((h, i) => h.padEnd(colWidths[i])).join(' '));
console.log(line);

const issues = [];
for (const r of rows) {
  const userDisplay = (r.user_display || '').slice(0, colWidths[2] - 1);
  console.log(
    [
      String(r.id).padEnd(colWidths[0]),
      String(r.season).padEnd(colWidths[1]),
      userDisplay.padEnd(colWidths[2]),
      (r.status || '').padEnd(colWidths[3]),
      String(r.driver_order_count ?? 0).padEnd(colWidths[4]),
      (r.fastest_lap_set || 'no').padEnd(colWidths[5]),
      String(r.dnf_count ?? 0).padEnd(colWidths[6]),
    ].join(' ')
  );
  if (!r.driver_order_count || r.driver_order_count < 1) {
    issues.push(`id=${r.id} (${r.user_display}): missing or empty driver_order`);
  }
}

if (issues.length) {
  console.log('');
  console.log('Issues (driver_order required):');
  issues.forEach((i) => console.log('  -', i));
}
console.log('');
console.log('(driver_order is required; fastest_lap and dnf_predictions are optional.)');
