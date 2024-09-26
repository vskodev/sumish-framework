<?php

namespace Sumish;

class ActiveRecord extends \ActiveRecord\Model {
    static $connection = 'mysql';

    public function __construct($config) {
        static::initialize($config);
    }

    public static function initialize($config = null) {
        \ActiveRecord\Config::initialize(function($cfg) use ($config) {
            $cfg->set_model_directory('.');
            $cfg->set_connections([
                'mysql' => self::parseDSN($config['db']['driver'], $config['db']['host'], $config['db']['database'], $config['db']['user'], $config['db']['password'], $config['db']['charset'])
            ]);
        });
    }

    public static function parseDSN($driver, $host, $database, $user, $password, $charset = 'utf8') {
        $dsn = '';
        if ($driver) { $dsn .= $driver . '://'; }
        if ($user) { $dsn .= $user . ':'; }
        if ($password) { $dsn .= $password . '@'; }
        if ($host) { $dsn .= $host; }
        if ($database) { $dsn .= '/' . $database; }
        $dsn .= '?charset=' . $charset;
        return $dsn;
    }
}