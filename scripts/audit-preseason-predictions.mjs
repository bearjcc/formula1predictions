/**
 * Audit preseason predictions on production DB: team_order, teammate_battles, red_flags, safety_cars, superlatives.
 * Requires PRODUCTION_DATABASE_URL in env.
 *
 * Run: node scripts/audit-preseason-predictions.mjs
 * Or:  PRODUCTION_DATABASE_URL="mysql://..." node scripts/audit-preseason-predictions.mjs
 */

import mysql from 'mysql2/promise';

const url = process.env.PRODUCTION_DATABASE_URL;
if (!url || !url.startsWith('mysql://')) {
  console.error('Set PRODUCTION_DATABASE_URL (e.g. mysql://user:pass@host:port/db)');
  process.exit(1);
}

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
    JSON_LENGTH(COALESCE(JSON_EXTRACT(p.prediction_data, '$.team_order'), '[]')) AS team_order_count,
    JSON_LENGTH(COALESCE(JSON_EXTRACT(p.prediction_data, '$.teammate_battles'), '{}')) AS teammate_battles_count,
    JSON_UNQUOTE(JSON_EXTRACT(p.prediction_data, '$.red_flags')) AS red_flags,
    JSON_UNQUOTE(JSON_EXTRACT(p.prediction_data, '$.safety_cars')) AS safety_cars,
    CASE
      WHEN JSON_EXTRACT(p.prediction_data, '$.superlatives') IS NULL THEN 0
      ELSE JSON_LENGTH(JSON_EXTRACT(p.prediction_data, '$.superlatives'))
    END AS superlatives_count
  FROM predictions p
  LEFT JOIN users u ON u.id = p.user_id
  WHERE p.type = 'preseason'
  ORDER BY p.season, p.user_id`
);
await conn.end();

if (rows.length === 0) {
  console.log('No preseason predictions found.');
  process.exit(0);
}

console.log('Preseason predictions:', rows.length);
console.log('');
const headers = ['id', 'season', 'user', 'status', 'team_order', 'teammate', 'red_flags', 'safety_cars', 'superlatives'];
const colWidths = [6, 8, 22, 12, 11, 10, 10, 12, 12];
const line = colWidths.map((w) => '-'.repeat(w)).join(' ');
console.log(headers.map((h, i) => h.padEnd(colWidths[i])).join(' '));
console.log(line);

const issues = [];
for (const r of rows) {
  const userDisplay = (r.user_display || '').slice(0, colWidths[2] - 1);
  const redFlags = r.red_flags != null && r.red_flags !== '' ? r.red_flags : '-';
  const safetyCars = r.safety_cars != null && r.safety_cars !== '' ? r.safety_cars : '-';
  console.log(
    [
      String(r.id).padEnd(colWidths[0]),
      String(r.season).padEnd(colWidths[1]),
      userDisplay.padEnd(colWidths[2]),
      (r.status || '').padEnd(colWidths[3]),
      String(r.team_order_count ?? 0).padEnd(colWidths[4]),
      String(r.teammate_battles_count ?? 0).padEnd(colWidths[5]),
      String(redFlags).padEnd(colWidths[6]),
      String(safetyCars).padEnd(colWidths[7]),
      String(r.superlatives_count ?? 0).padEnd(colWidths[8]),
    ].join(' ')
  );
  if (!r.team_order_count || r.team_order_count < 1) {
    issues.push(`id=${r.id} (${r.user_display}): missing or empty team_order`);
  }
}

if (issues.length) {
  console.log('');
  console.log('Issues (team_order required):');
  issues.forEach((i) => console.log('  -', i));
}
console.log('');
console.log('(team_order is required; teammate_battles, red_flags, safety_cars, superlatives are optional.)');
