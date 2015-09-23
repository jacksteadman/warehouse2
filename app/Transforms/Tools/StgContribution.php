<?php

namespace App\Transforms\Tools;

class StgContribution {
    private $fixture_data = [];

    private $output_format = [
        'client_id',
        'stg_contribution_id',
    ];

    private $filters = [
        'source' => [ 'filterString' ],
        'subsource' => [ 'filterString' ],
    ];

    public function setFixtureData($data) {
        $this->fixture_data = $data;
    }

    public function transform($record) {
        $record = array_merge($this->fixture_data, $record);

        $tx_record = [];
        foreach ($this->output_format as $field) {
            $value = (isset($record[$field]) ? $record[$field] : null);

            if (!empty($this->filters[$field])) {
                foreach ($this->filters[$field] as $filter) {
                    if (method_exists($this, $filter)) {
                        $value = $this->$filter($value);
                    } elseif (is_callable($filter)) {
                        $value = $filter($value);
                    }
                }
            }

            $tx_record[] = $value;
        }
    }

    private function filterString($string) {
        return $string;
    }
}