<?php

namespace App\Extracts;

class Tools {
    private $db_name;
    private $table_name;
    private $pk_name;

    private $extract_file_prefix;

    private $file_size_check_interval = 10000;
    private $file_size_limit = 1000000000;         // 1GB uncompressed

    private $extra_data = array();

    private $table_transform;



    public function __construct($db_name, $table_name, $pk_name, $extract_file_prefix) {
        $this->db_name = $db_name;
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

    public function addExtraData($field, $value) {
        $this->extra_data[$field] = $value;
    }

    private function getMinMaxIds() {

    }








}