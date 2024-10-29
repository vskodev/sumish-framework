<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use PDO;
use PDOException;

/**
 * Абстрактный класс для работы с базой данных через PDO.
 *
 * Этот класс предоставляет базовую функциональность для подключения 
 * к базе данных с использованием PDO (PHP Data Objects). Он 
 * реализует методы для установки соединения и создания 
 * строк подключения (DSN), которые могут быть использованы 
 * конкретными моделями для взаимодействия с базой данных.
 *
 * @package Sumish
 */
abstract class ModelDb {
    /**
     * Экземпляр соединения с базой данных.
     *
     * @var PDO
     */
    protected $db;

    /**
     * Конструктор класса ModelDb.
     *
     * Этот метод инициализирует соединение с базой данных 
     * на основе переданной конфигурации.
     *
     * @param array $config Конфигурация для подключения к базе данных.
     */
    public function __construct($config) {
        $this->db = static::connect($config);
    }

    /**
     * Устанавливает соединение с базой данных.
     *
     * Этот метод создает экземпляр PDO для подключения к базе данных 
     * с использованием переданной конфигурации. Он реализует 
     * паттерн Singleton для обеспечения единственного соединения.
     *
     * @param array $config Конфигурация для подключения к базе данных.
     * @return PDO|null Возвращает экземпляр PDO, если соединение успешно, 
     *                  или null в случае ошибки.
     */
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

    /**
     * Формирует строку подключения DSN для PDO.
     *
     * Этот метод создает строку подключения (Data Source Name) 
     * на основе переданных параметров.
     *
     * @param string $driver Драйвер базы данных.
     * @param string $host Хост базы данных.
     * @param string $database Имя базы данных.
     * @param string $charset Кодировка (по умолчанию 'utf8').
     * @return string Возвращает сформированную строку DSN.
     */
    public static function parseDSN($driver, $host, $database, $charset = 'utf8') {
        $dsn = '';
        if ($driver) { $dsn .= $driver . ':'; }
        if ($host) { $dsn .= 'host=' . $host .';'; }
        if ($database) { $dsn .= 'dbname=' . $database . ';'; }
        $dsn .= 'charset=' . $charset;
        return $dsn;
    }
}
