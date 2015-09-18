<?php

namespace App\Services;

use App\Contracts\ClientDb;

class ClientDbService implements ClientDb\Service {

    public function getPdoConnection($client_slug) {
        // TODO: put path and bundle name in configs
        $bundle_file = '/etc/bluestate/bundles/' . $client_slug . '/masterdb.json';
        if (!file_exists($bundle_file)) {
            throw new Exception('Bundle ' . $bundle_file . ' does not exist.');
        }

        $bundle_json = file_get_contents($bundle_file);
        if ($bundle_json === false) {
            throw new Exception('Bundle ' . $bundle_file . ' is empty or could not be read.');
        }

        $bundle = json_decode($bundle_json, true);
        if (is_null($bundle)) {
            throw new Exception('Bundle ' . $bundle_file . ' did not contain valid json.');
        }

        if (!$this->isValidBundle($bundle)) {
            throw new Exception('Bundle ' . $bundle_file . ' does not appear to be a valid database config bundle.');
        }

        $host = str_replace('fwork-master', 'fwork-reporting', $bundle['db_host']);

        $conn_options = array();
        if (isset($bundle['db_ssl']) && $bundle['db_ssl']) {
            $conn_options[PDO::MYSQL_ATTR_SSL_CA] = $bundle['db_ssl_ca'];
        }

        try {
            $conn = new PDO(
                'mysql:host=' . $host . ';port=' . $bundle['db_port'] . ';dbname=' . $bundle['db_name'],
                $bundle['db_user'],
                $bundle['db_pass'],
                $conn_options
            );

            if (!$conn) {
                throw new Exception('Could not connect');
            }

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;

        } catch (Exception $e) {
            throw new Exception("Failed connecting to client DB $client_slug: " . $e);
        }
    }

    private function isValidBundle($bundle) {
        return (
            isset($bundle['db_host'])
            &&
            isset($bundle['db_port'])
            &&
            isset($bundle['db_name'])
            &&
            isset($bundle['db_user'])
            &&
            isset($bundle['db_pass'])
        );
    }
}