<?php

namespace App\Extracts;

use App\Contracts\ToolsClient;
use App\Transforms;

class Tools {
    const REALM = 'tools';

    private $db_service;
    private $tx_factory;

    private $client_slug;
    private $table_name;
    private $pk_name;

    private $extract_file_prefix;

    // TODO config these
    private $record_batch_size = 10000;
    private $file_size_check_interval = 10000;
    private $file_size_limit = 1000000000;         // 1GB uncompressed

    private $progress_bar;

    private $fixture_data = array();


    public function __construct(ToolsClient\Service $db_service) {
        $this->db_service = $db_service;
        $this->tx_factory = new Transforms\Factory();
    }

    public function configure($client_slug, $table_name, $pk_name, $extract_file_prefix) {
        $this->client_slug = $client_slug;
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

    public function setProgressBar($pb) {
        $this->progress_bar = $pb;
    }

    public function addFixtureData($field, $value) {
        $this->fixture_data[$field] = $value;
    }

    private function getMinMaxIds(\PDO $conn) {
        $sql = 'SELECT MIN(' . $this->pk_name . '), MAX(' . $this->pk_name . ') FROM ' . $this->table_name;
        $sth = $conn->query($sql);
        if ($row = $sth->fetch(\PDO::FETCH_NUM)) {
            return $row;
        }
        return [ 0, 0 ];
    }

    private function writeRecord($fp, $record) {
        fputcsv($fp, $record);
    }

    public function extractAll() {
        $transform = $this->tx_factory->make(self::REALM, $this->table_name);
        $transform->setFixtureData($this->fixture_data);

        $iterator = new TableIterator();

        $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE ' . $this->pk_name . ' BETWEEN :start_id AND :end_id';
        $iterator->sql($sql);

        $conn = $this->db_service->getPdoConnection($this->client_slug);
        $iterator->conn($conn);

        list($min_id, $max_id) = $this->getMinMaxIds($conn);

        // crude approximation of record count
        $total_records = $max_id - $min_id + 1;

        $file_index = 0;
        $current_file = $this->extract_file_prefix . '.' . $file_index;

        $fp = fopen($current_file, 'w');
        // TODO errors

        $processed = 0;
        $current_percentile = 0;

        for ($i = $min_id; $i <= $max_id; $i += $this->record_batch_size) {
            if (($processed % $this->file_size_check_interval)) {
                clearstatcache();
                $size = filesize($current_file);

                if ($size > $this->file_size_limit) {
                    fclose($fp);
                    $file_index++;
                    $current_file = $this->extract_file_prefix . '.' . $file_index;
                    $fp = fopen($current_file, 'w');

                    // TODO errors
                }
            }

            $iterator->nextBatch($min_id, $min_id + $this->record_batch_size - 1);

            while ($record = $iterator->nextRecord()) {
                $tx_record = $transform->transform($record);
                $this->writeRecord($fp, $tx_record);
            }

            $processed++;

            $percentile = floor($processed / $total_records * 100);
            if ($this->progress_bar && $percentile > $current_percentile) {
                $this->progress_bar->advance();
                $current_percentile = $percentile;
            }
        }
    }
}