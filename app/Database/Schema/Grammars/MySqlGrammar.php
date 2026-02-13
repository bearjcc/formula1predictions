<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

/**
 * MySQL schema grammar that avoids SYSTEM VERSIONED in table-existence checks
 * and avoids schema() so migrations work on Railway/MySQL where "near '10'"
 * syntax errors can occur (e.g. when schema is misparsed or schema() is used).
 */
class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile the query to determine if the given table exists.
     * Uses the connection's database name (quoted) instead of schema() so
     * the query never embeds a raw integer or triggers MySQL syntax errors.
     */
    public function compileTableExists($schema, $table): string
    {
        $schemaValue = ($schema !== null && $schema !== '')
            ? (string) $schema
            : $this->connection->getDatabaseName();

        return sprintf(
            'select exists (select 1 from information_schema.tables where '
            ."table_schema = %s and table_name = %s and table_type = 'BASE TABLE') as `exists`",
            $this->quoteString($schemaValue),
            $this->quoteString($table)
        );
    }

    /**
     * Compile the query to determine the tables.
     */
    public function compileTables($schema): string
    {
        return sprintf(
            'select table_name as `name`, table_schema as `schema`, (data_length + index_length) as `size`, '
            .'table_comment as `comment`, engine as `engine`, table_collation as `collation` '
            ."from information_schema.tables where table_type = 'BASE TABLE' and "
            .$this->compileSchemaWhereClause($schema, 'table_schema')
            .' order by table_schema, table_name',
            $this->quoteString($schema)
        );
    }
}
