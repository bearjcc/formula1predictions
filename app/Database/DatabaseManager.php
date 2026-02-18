<?php

namespace App\Database;

use Illuminate\Database\DatabaseManager as BaseDatabaseManager;

/**
 * Ensures mysql/mariadb 'database' config is always a string.
 * Laravel's ConfigurationUrlParser can parse "10" as int via json_decode(),
 * causing SQL syntax error "near '10'" on information_schema queries.
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
        $config = parent::configuration($name);

        $driver = $config['driver'] ?? null;
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // When DB_URL is set, URL parser overwrites DB_DATABASE. If the URL path is numeric
            // (e.g. /10), json_decode yields int and causes "near '10'" SQL errors. When the
            // merged database is an integer, prefer explicit DB_DATABASE from config.
            $parsedDb = $config['database'] ?? null;
            $explicitDb = $this->app['config']->get("database.connections.{$name}.database");
            if (is_int($parsedDb) && $explicitDb !== null && $explicitDb !== '') {
                $config['database'] = (string) $explicitDb;
            } elseif (isset($config['database'])) {
                $config['database'] = (string) $config['database'];
            }
        }

        return $config;
    }
}
