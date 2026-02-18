<?php

namespace App\Database;

use Illuminate\Database\DatabaseManager as BaseDatabaseManager;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;

/**
 * Ensures mysql/mariadb 'database' config is always a string.
 * Laravel's ConfigurationUrlParser parses URL path through parseStringsToNativeTypes
 * which uses json_decode("10")->int. Integer database name causes MySQL "near '10'" errors.
 * We ALWAYS prefer explicit DB_DATABASE from config over URL-parsed value.
 */
class DatabaseManager extends BaseDatabaseManager
{
    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration($name)
    {
        $connections = $this->app['config']['database.connections'] ?? [];
        $rawConfig = Arr::get($connections, $name, []);
        $explicitDb = $rawConfig['database'] ?? null;

        $config = parent::configuration($name);

        $driver = $config['driver'] ?? null;
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $parsedDb = $config['database'] ?? null;
            // When URL path is numeric (e.g. /10), parseStringsToNativeTypes yields int.
            // Integer database name causes MySQL "near '10'" syntax errors. Prefer explicit.
            if (is_int($parsedDb) && $explicitDb !== null && $explicitDb !== '') {
                $config['database'] = (string) $explicitDb;
            } elseif (isset($config['database'])) {
                $config['database'] = (string) $config['database'];
            }
            // Log when we corrected an int to aid debugging (avoid breaking boot if logger unavailable)
            if (is_int($parsedDb)) {
                try {
                    if ($this->app->bound(LoggerInterface::class)) {
                        $this->app->make(LoggerInterface::class)->warning(
                            'Railway MySQL: URL parsed database as int, using explicit DB_DATABASE',
                            ['parsed' => $parsedDb, 'using' => $config['database']]
                        );
                    }
                } catch (\Throwable) {
                    // Ignore
                }
            }
        }

        return $config;
    }
}
