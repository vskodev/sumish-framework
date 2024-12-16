<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

namespace Sumish\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Класс Connection для управления подключением к базе данных.
 *
 * @package Sumish\Database
 */
class Connection {
    /**
     * @var PDO|null Объект подключения к базе данных.
     */
    private static ?PDO $db = null;

    /**
     * Открывает соединение с базой данных.
     *
     * @param array{driver: string, host: string, database: string, charset: string, user: string, password: string} $config Параметры подключения.
     * @return PDO Объект подключения.
     * @throws RuntimeException Если подключение не удалось.
     */
    public static function open(array $config): PDO {
        if (self::$db === null) {
            $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $username = $config['user'];
            $password = $config['password'];

            try {
                self::$db = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection error: ' . $e->getMessage(), 0, $e);
            }
        }

        return self::$db;
    }

    /**
     * Возвращает текущее соединение с базой данных.
     *
     * @return PDO Объект подключения.
     * @throws RuntimeException Если соединение не было установлено.
     */
    public static function get(): PDO {
        if (self::$db === null) {
            throw new RuntimeException('Database connection is not initialized.');
        }

        return self::$db;
    }

    /**
     * Закрывает текущее соединение с базой данных.
     *
     * @return void
     */
    public static function close(): void {
        self::$db = null;
    }
}
