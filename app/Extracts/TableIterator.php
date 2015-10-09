<?php

namespace App\Extracts;

use PDO;

class TableIterator {

    private $sql;
    private $conn;

    private $sth;

    public function conn(PDO $conn) {
        $this->conn = $conn;
    }

    public function sql($sql) {
        if (strpos($sql, ':start_id') === false || strpos($sql, ':end_id') === false) {
            throw new Exception('SQL must contain both :start_id and :end_id parameters.');
        }
        $this->sql = $sql;
    }

    public function nextBatch($start, $end) {
        try {
            if (!isset($this->sth)) {
                $this->sth = $this->conn->prepare($this->sql);
            } else {
                $this->sth->closeCursor();
            }

            $this->sth->bindParam(':start_id', $start);
            $this->sth->bindParam(':end_id', $end);
            $this->sth->execute();

        } catch (Exception $e) {
            // TODO
            throw $e;
        }
    }

    public function nextRecord() {
        if (!isset($this->sth)) {
            throw new Exception('No statement handle found.');
        }

        return $this->sth->fetch(PDO::FETCH_ASSOC);
    }
}