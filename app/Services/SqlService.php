<?php

namespace App\Services;

use Exception;

class SqlService {
    public function stagingTableFromToolsTable($table_name, $pk_name, $mysql_desc) {
        $field_list_ddl = [];
        foreach ($mysql_desc as $field_def) {
            $pg_type = $this->mysqlTypeToPostgresType($field_def['Type'], $field_def['Null']);

            $field_list_ddl[] = $field_def['Field']
                . ' ' . $pg_type['data_type']
                . ' ' . ($pg_type['null'] ? 'NULL' : 'NOT NULL')
            ;
        }

        $ddl = 'CREATE TABLE ' . $table_name . " (\n"
            . '    client_id integer not null,' . "\n"
            . '    ' . join(",\n    ", $field_list_ddl)
            . ")\n"
            . 'PRIMARY KEY (client_id, ' . $pk_name . ")\n"
            . 'DISTSTYLE KEY' . "\n"
            . 'DISTKEY (client_id)' . "\n"
        ;

        return $ddl;
    }

    public function mysqlTypeToPostgresType($type, $null) {
        if (!preg_match('/^(?<base_type>\w+)(?<size>\(.+?\))?/', $type, $matches)) {
            throw new Exception('Unable to parse mysql type string: ' . $type);
        }

        $pg_type = [ 'null' => true ];
        if ($null == 'NO') {
            $pg_type['null'] = false;
        }

        switch (strtolower($matches['base_type'])) {
            case 'tinyint':
            case 'smallint':
                $pg_type['data_type'] = 'smallint';
                break;
            case 'int':
            case 'integer':
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
            case 'numeric':
                $pg_type['data_type'] = 'decimal(' . $matches['size'] . ')';
                break;
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
            case 'text':
            case 'enum':
                $pg_type['data_type'] = 'varchar(MAX)';
                break;
            case 'date':
                $pg_type['data_type'] = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $pg_type['data_type'] = 'timestamp';
                break;
            default:
                throw new Exception('Unrecognized mysql type: ' . $type);
        }

        return $pg_type;
    }
}