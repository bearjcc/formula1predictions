<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

/**
 * MySQL schema grammar that avoids SYSTEM VERSIONED in table-existence checks.
 * Uses only 'BASE TABLE' so migrations work on MySQL/MariaDB versions and
 * environments (e.g. Railway) where the default Laravel query can trigger
 * SQLSTATE[42000] syntax errors.
 */
class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile the query to determine if the given table exists.
     */
    public function compileTableExists($schema, $table): string
    {
        return sprintf(
            'select exists (select 1 from information_schema.tables where '
            ."table_schema = %s and table_name = %s and table_type = 'BASE TABLE') as `exists`",
            $schema ? $this->quoteString($schema) : 'schema()',
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
