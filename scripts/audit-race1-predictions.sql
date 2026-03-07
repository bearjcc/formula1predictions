-- Audit race 1 predictions: order, fastest_lap, dnf_predictions
-- Run: railway connect MySQL, then paste this (or: mysql ... railway < scripts/audit-race1-predictions.sql)

SELECT
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
ORDER BY p.season, p.user_id;
