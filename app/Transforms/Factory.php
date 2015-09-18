<?php

namespace App\Transforms;

class Factory {
    public function make($realm, $source_table) {
        $transform_class = ucfirst($realm) . '\\' . join('', array_map('ucfirst', explode('_', $source_table)));
        return new $transform_class;
    }
}