<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

/**
 * MySQL schema grammar that avoids SYSTEM VERSIONED in table-existence checks,
 * avoids schema() so migrations work on Railway/MySQL, and uses alias `has_table`
 * instead of reserved `exists` to prevent parser syntax errors on some MySQL/MariaDB.
 */
class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile the query to determine if the given table exists.
     * Uses the connection's database name (quoted) instead of schema() so
     * the query never embeds a raw integer or triggers MySQL syntax errors.
     * Forces schema/database to string so numeric values (e.g. from misparsed DB_URL) become '10' not 10.
     */
    public function compileTableExists($schema, $table): string
    {
        $schemaValue = ($schema !== null && $schema !== '')
            ? (string) $schema
            : $this->connection->getDatabaseName();

        return sprintf(
            'select exists (select 1 from information_schema.tables where '
            ."table_schema = %s and table_name = %s and table_type = 'BASE TABLE') as `has_table`",
            $this->quoteString((string) $schemaValue),
            $this->quoteString((string) $table)
        );
    }

    /**
     * Compile the query to determine the tables.
     * Uses quoted schema/database name (never schema()) to avoid "near '10'" on Railway/MySQL.
     */
    public function compileTables($schema): string
    {
        $schemaValue = ($schema !== null && $schema !== '')
            ? (string) $schema
            : (string) $this->connection->getDatabaseName();

        return sprintf(
            'select table_name as `name`, table_schema as `schema`, (data_length + index_length) as `size`, '
            .'table_comment as `comment`, engine as `engine`, table_collation as `collation` '
            ."from information_schema.tables where table_type = 'BASE TABLE' and table_schema = %s "
            .'order by table_schema, table_name',
            $this->quoteString($schemaValue)
        );
    }
}
