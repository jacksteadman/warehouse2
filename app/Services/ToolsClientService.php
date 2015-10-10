<?php

namespace App\Services;

use Exception;
use PDO;
use App\Contracts\ToolsClient;

class ToolsClientService implements ToolsClient\Service {

    private static $bundle_cache = [];

    public function getClientId($client_slug) {
        $bundle = $this->loadBundle($client_slug);
        return $bundle['client_id'];
    }

    public function getCharset($client_slug) {
        $bundle = $this->loadBundle($client_slug);
        return $bundle['default_charset'];
    }

    public function getTimezone($client_slug) {
        $bundle = $this->loadBundle($client_slug);
        return $bundle['system_timezone'];
    }

    public function getPdoConnection($client_slug) {
        $bundle = $this->loadBundle($client_slug);

        // this transformation is done elsewhere in places like access_client_db
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

    private function loadBundle($client_slug) {
        if (!empty(self::$bundle_cache[$client_slug])) {
            return self::$bundle_cache[$client_slug];
        }

        $bundle_file = config('warehouse.tools_bundle_root') . '/' . $client_slug . '/' . config('warehouse.tools_bundle_name');
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

        self::$bundle_cache[$client_slug] = $bundle;
        return $bundle;
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