<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verbose DB config diagnostic for Railway "near '10'" debugging.
 * Run before migrate to capture config state and connection details.
 */
class DbDiagnoseCommand extends Command
{
    protected $signature = 'db:diagnose';

    protected $description = 'Diagnose DB config and connection (Railway near 10 debug)';

    public function handle(): int
    {
        $out = function (string $msg, array $ctx = []) {
            $line = '[DB_DIAG] '.$msg;
            if ($ctx !== []) {
                $line .= ' | '.json_encode($ctx, JSON_THROW_ON_ERROR);
            }
            $this->line($line);
            error_log($line);
        };

        $out('db:diagnose start');

        if (config('database.default') !== 'mysql') {
            $out('skip: default connection is not mysql', ['default' => config('database.default')]);

            return 0;
        }

        $mysql = config('database.connections.mysql');
        if (! is_array($mysql)) {
            $out('mysql config missing or not array');

            return 1;
        }

        $out('mysql config keys', array_keys($mysql));
        $out('database key', [
            'value' => $mysql['database'] ?? 'NOT SET',
            'type' => gettype($mysql['database'] ?? null),
            'is_int' => is_int($mysql['database'] ?? null),
        ]);
        $out('url present', ['has_url' => ! empty($mysql['url'])]);
        if (! empty($mysql['url'])) {
            $parsed = parse_url($mysql['url']);
            $out('parsed url path', [
                'path' => $parsed['path'] ?? 'N/A',
                'path_first_char' => isset($parsed['path']) ? bin2hex($parsed['path'][0] ?? '') : 'N/A',
            ]);
        }
        $out('host', ['host' => $mysql['host'] ?? 'N/A']);
        $out('port', ['port' => $mysql['port'] ?? 'N/A', 'type' => gettype($mysql['port'] ?? null)]);

        try {
            $pdo = DB::connection('mysql')->getPdo();
            $out('PDO connected');
            $out('current database (PDO)', [
                'name' => $pdo->query('SELECT DATABASE()')->fetchColumn(),
                'type' => 'from DATABASE()',
            ]);

            $query = "show tables like 'migrations'";
            $out('running query', ['sql' => $query, 'bytes' => bin2hex($query)]);
            $stmt = $pdo->query($query);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_NUM) : [];
            $out('SHOW TABLES result', ['row_count' => count($rows), 'ok' => true]);
        } catch (\Throwable $e) {
            $out('connection or query failed', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            $this->error($e->getMessage());

            return 1;
        }

        $out('db:diagnose done');

        return 0;
    }
}
