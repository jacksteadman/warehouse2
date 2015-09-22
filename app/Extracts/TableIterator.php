<?php

namespace App\Extracts;

class TableIterator {

    private $sql;
    private $conn;

    private $sth;

    public function connection($conn) {
        $this->conn = $conn;
    }

    public function sql($sql) {
        $this->sql = $sql;
    }






    public function nextBatch($start, $end) {

    }

    public function nextRecord() {

    }




}