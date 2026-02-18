<?php

/**
 * Standalone DB diagnostic - no Laravel. Runs before migrate to capture raw state.
 * Usage: php scripts/db-raw-diagnose.php
 */
$log = function (string $msg, $ctx = null) {
    $line = '[DB_RAW] '.$msg;
    if ($ctx !== null) {
        $line .= ' | '.json_encode($ctx, JSON_THROW_ON_ERROR);
    }
    echo $line.PHP_EOL;
    error_log($line);
};

$log('start');

$dbUrl = getenv('DB_URL');
$dbDatabase = getenv('DB_DATABASE');

$log('env', [
    'DB_URL_set' => $dbUrl !== false,
    'DB_URL_length' => $dbUrl !== false ? strlen($dbUrl) : 0,
    'DB_DATABASE' => $dbDatabase,
    'DB_DATABASE_type' => gettype($dbDatabase),
]);

if (! $dbUrl) {
    $log('DB_URL not set, skipping raw connect');
    exit(0);
}

$parsed = parse_url($dbUrl);
$log('parsed url', [
    'host' => $parsed['host'] ?? 'N/A',
    'port' => $parsed['port'] ?? 'N/A',
    'path' => $parsed['path'] ?? 'N/A',
    'path_raw' => isset($parsed['path']) ? bin2hex($parsed['path']) : 'N/A',
]);

$path = $parsed['path'] ?? '/';
$dbFromPath = $path !== '/' && $path !== '' ? substr($path, 1) : null;
$log('db from path', ['value' => $dbFromPath, 'json_decode' => @json_decode((string) $dbFromPath)]);

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s',
    $parsed['host'] ?? '127.0.0.1',
    $parsed['port'] ?? 3306,
    $dbDatabase ?: $dbFromPath ?: 'mysql'
);
$log('dsn dbname', ['used' => $dbDatabase ?: $dbFromPath ?: 'mysql']);

try {
    $pdo = new PDO(
        $dsn,
        isset($parsed['user']) ? rawurldecode($parsed['user']) : 'root',
        isset($parsed['pass']) ? rawurldecode($parsed['pass']) : '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $log('PDO connected');
    $log('DATABASE()', ['result' => $pdo->query('SELECT DATABASE()')->fetchColumn()]);

    $q = "show tables like 'migrations'";
    $log('running', ['query' => $q]);
    $stmt = $pdo->query($q);
    $log('result', ['rows' => $stmt->rowCount()]);
} catch (Throwable $e) {
    $log('FAILED', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);
    exit(1);
}

$log('done');
exit(0);
