<?php

namespace App\Services;

use Exception;
use PDO;

class SqlService {
    public function stagingTableFromTools($table_name, $pk_name, $mysql_desc) {
        $ddl = 'CREATE TABLE ' . $table_name . ' ( client_id integer not null, ';
        foreach ($desc as $field_def) {
            $pg_type = $this->mysqlTypeToPostgresType($field_def['Type'], $field_def['Null']);

            $ddl .= $field_def['Field']
                . $pg_type['data_type']
                . ($pg_type['null'] ? ' NULL ' : ' NOT NULL ')
                . ', '
            ;
        }

        // TODO distkey, sortkey
        $ddl .= ' ) '
            . ' PRIMARY KEY (client_id, ' . $pk_name . ')'
        ;

        return $ddl;
    }

    public function mysqlTypeToPostgresType($type, $null) {
        $pg_type = [ 'null' => true ];

        preg_match('/^(?<base_type>\w+)(?<size>\([\d,]+\))?', $type, $matches);

        switch ($matches['base_type']) {
            case 'tinyint':
            case 'smallint':
                $pg_type['data_type'] = 'smallint';
                break;
            case 'int':
            case 'mediumint':
                $pg_type['data_type'] = 'integer';
                break;
            case 'bigint':
                $pg_type['data_type'] = 'bigint';
                break;
            case 'float':
                $pg_type['data_type'] = 'float';
                break;
            case 'decimal':
                $pg_type['data_type'] = 'decimal(' . $matches['size'] . ')';
                break;
            case 'char':
            case 'varchar':
            case 'text':
                $pg_type['data_type'] = 'varchar(MAX)';
                break;
            case 'date':
                $pg_type['data_type'] = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $pg_type['data_type'] = 'timestamp';
                break;
        }


        if ($null == 'NO') {
            $pg_type['null'] = false;
        }

        return $pg_type;
    }
}