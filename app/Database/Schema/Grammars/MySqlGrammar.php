<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

/**
 * MySQL schema grammar that avoids SYSTEM VERSIONED and works around Railway/MySQL
 * "near '10'" errors. Uses database() so no schema value is passed from PHP.
 */
class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile the query to determine if the given table exists.
     * Uses SHOW TABLES (no information_schema). Table name quoted inline.
     */
    public function compileTableExists($schema, $table): string
    {
        $tableQuoted = $this->quoteString((string) $table);

        return "show tables like {$tableQuoted}";
    }

    /**
     * Compile the query to determine the tables.
     * Uses database() to avoid schema value issues.
     */
    public function compileTables($schema): string
    {
        return 'select table_name as `name`, table_schema as `schema`, '
            .'(data_length + index_length) as `size`, table_comment as `comment`, '
            .'engine as `engine`, table_collation as `collation` '
            .'from information_schema.tables '
            ."where table_type = 'BASE TABLE' and table_schema = database() "
            .'order by table_schema, table_name';
    }
}
