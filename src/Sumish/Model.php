<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use PDO;

/**
 * Базовый класс для моделей.
 *
 * Обеспечивает подключение к базе данных через PDO
 * и может расширяться для реализации бизнес-логики.
 *
 * @package Sumish
 */
abstract class Model {
    /**
     * Экземпляр PDO.
     * @var PDO
     */
    protected PDO $db;

    /**
     * Название таблицы.
     * Если не указано, будет определяться на основе имени класса.
     * @var string
     */ 
    protected string $table;

    /**
     * Конструктор модели.
     * Устанавливает соединение с базой данных.
     *
     * @param array $config Конфигурация для подключения к базе данных.
     */
    public function __construct(array $config) {
        if (!isset($config['db'])) {
            throw new \Exception("Database configuration missing in 'db' key.");
        }

        $this->db = static::connect($config['db']);

        if (!isset($this->table)) {
            $this->table = strtolower((new \ReflectionClass($this))->getShortName());
        }
    }

    /**
     * Создаёт подключение к базе данных.
     *
     * @param array $config Конфигурация подключения.
     * @return PDO Экземпляр PDO.
     */
    protected static function connect(array $config): PDO {
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $username = $config['user'];
        $password = $config['password'];

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true,
            ]);
        } catch (\PDOException $e) {
            throw new \Exception('Database connection error: ' . $e->getMessage());
        }
    }

    /**
     * Получить запись по ID.
     *
     * @param int $id ID записи.
     * @return array|null Данные записи или null, если не найдено.
     */
    public function find(int $id): ?array {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $query->execute(['id' => $id]);
        return $query->fetch() ?: null;
    }

    /**
     * Получить все записи.
     *
     * @return array Список записей.
     */
    public function all(): array {
        $query = $this->db->query("SELECT * FROM {$this->table}");
        return $query->fetchAll();
    }

    /**
     * Удалить запись по ID.
     *
     * @param int $id ID записи.
     * @return bool Успех удаления.
     */
    public function delete(int $id): bool {
        $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $query->execute(['id' => $id]);
    }

    /**
     * Сохранить данные в таблице.
     * Если передан ID, обновить запись; иначе вставить новую.
     *
     * @param array $data Данные для сохранения.
     * @return bool Успех операции.
     */
    public function save(array $data): bool {
        if (isset($data['id'])) {
            $columns = array_keys($data);
            $sets = implode(', ', array_map(fn($col) => "{$col} = :{$col}", $columns));
            $query = $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE id = :id");
        } else {
            $columns = array_keys($data);
            $placeholders = implode(', ', array_map(fn($col) => ":{$col}", $columns));
            $query = $this->db->prepare("INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})");
        }
        return $query->execute($data);
    }
}
