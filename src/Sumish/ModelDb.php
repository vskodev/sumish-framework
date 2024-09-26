<?php

namespace Sumish;

use PDO;
use PDOException;

abstract class ModelDb {
    protected $db;

    public function __construct($config) {
        $this->db = static::connect($config);
    }

    protected static function connect($config) {
        static $db = null;

        if (is_null($db)) {
            try {
                $dsn = self::parseDSN($config['db']['driver'], $config['db']['host'], $config['db']['database'], $config['db']['charset']);
                $db = new PDO($dsn, $config['db']['user'], $config['db']['password']);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                trigger_error($e->getMessage());
            }
        }

        return $db;
    }

    public static function parseDSN($driver, $host, $database, $charset = 'utf8') {
        $dsn = '';
        if ($driver) { $dsn .= $driver . ':'; }
        if ($host) { $dsn .= 'host=' . $host .';'; }
        if ($database) { $dsn .= 'dbname=' . $database . ';'; }
        $dsn .= 'charset=' . $charset;
        return $dsn;
    }
}
