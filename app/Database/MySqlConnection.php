<?php

namespace App\Database;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

/**
 * Uses PDO::query() instead of prepare() for SHOW TABLES to avoid Railway
 * "near '10'" errors that may occur with prepared SHOW statements.
 */
class MySqlConnection extends BaseMySqlConnection
{
    /**
     * Run a select statement and return the first row, using query() for SHOW.
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        if ($this->isShowTablesQuery($query) && $bindings === []) {
            return $this->selectOneViaQuery($query, $useReadPdo);
        }

        return parent::selectOne($query, $bindings, $useReadPdo);
    }

    private function isShowTablesQuery(string $query): bool
    {
        return preg_match('/^\s*show\s+tables\s+like\s+/i', $query) === 1;
    }

    private function selectOneViaQuery(string $query, bool $useReadPdo)
    {
        $rows = $this->run($query, [], function ($query) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $pdo = $this->getPdoForSelect($useReadPdo);
            $statement = $pdo->query($query);

            return $statement ? $statement->fetchAll(\PDO::FETCH_NUM) : [];
        });

        if (empty($rows)) {
            return null;
        }
        $value = $rows[0][0] ?? null;

        // scalar() expects object with one property; truthy = table exists
        return (object) ['has_table' => $value !== null && $value !== '' && $value !== false];
    }
}
