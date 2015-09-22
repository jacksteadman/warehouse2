<?php

namespace App\Extracts;

use App\Contracts\ClientDb;
use App\Transforms;

class Tools {
    private $db_service;
    private $tx_factory;

    private $table_name;
    private $pk_name;

    private $extract_file_prefix;

    private $record_batch_size = 10000;
    private $file_size_check_interval = 10000;
    private $file_size_limit = 1000000000;         // 1GB uncompressed

    private $fixture_data = array();


    public function __construct(ClientDb\Service $db_service) {
        $this->db_service = $db_service;
        $this->tx_factory = new Transforms\Factory();
    }

    public function configure($table_name, $pk_name, $extract_file_prefix) {
        $this->table_name = $table_name;
        $this->pk_name = $pk_name;
        $this->extract_file_prefix = $extract_file_prefix;
    }

    public function setFileSizeCheckInterval($interval) {
        $this->file_size_check_interval = $interval;
    }

    public function setFileSizeLimit($limit) {
        $this->file_size_limit = $limit;
    }

    public function addFixtureData($field, $value) {
        $this->fixture_data[$field] = $value;
    }

    private function getMinMaxIds() {

    }

    private function fetchRecordBatch($start_id, $end_id) {
        return array();

    }

    private function writeRecord($record) {

    }




    public function extract() {
        $transform = $this->tx_factory->make('tools', $this->table_name);


        for ($i = $min_id; $i <= $max_id; $i += $this->record_batch_size) {
            $records = $this->fetchRecordBatch($min_id, $min_id + $record_batch_size - 1);

            foreach ($records as $record) {
                $tx_record = $transform->transform($record);

                $this->writeRecord($tx_record);
            }

        }

    }
}