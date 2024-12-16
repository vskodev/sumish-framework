<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

namespace Sumish\Database;

use PDO;
use ArrayObject;
use ReflectionClass;
use InvalidArgumentException;

/**
 * Базовый класс для моделей.
 *
 * Подключает базу данных через PDO и предоставляет общие методы для работы с данными.
 *
 * @package Sumish\Database
 */
abstract class Model {
    /**
     * Объект подключения к базе данных.
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * Имя таблицы.
     *
     * @var string
     */
    protected string $table;

    /**
     * Название первичного ключа.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Условия для SQL-запросов.
     *
     * @var array<array{column: string, operator: string, value: mixed}>
     */
    protected array $conditions = [];

    /**
     * Поле сортировки.
     *
     * @var string|null
     */
    protected ?string $orderBy = null;

    /**
     * Направление сортировки.
     *
     * @var string|null
     */
    protected ?string $orderDirection = null;

    /**
     * Лимит выборки.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Инициализация соединения с базой данных и определение таблицы.
     *
     * @param ArrayObject<string, mixed> $config Конфигурация для подключения к базе данных.
     * @throws InvalidArgumentException Если конфигурация базы данных отсутствует.
     */
    public function __construct(ArrayObject $config) {
        if (!isset($config['db'])) {
            throw new InvalidArgumentException("Database configuration missing in 'db' key.");
        }
        $this->db = Connection::open($config['db']);
        if (!isset($this->table)) {
            $this->table = strtolower((new ReflectionClass($this))->getShortName());
        }
    }

    /**
     * Добавляет условия в запрос.
     *
     * @param array<string, mixed> $conditions Массив условий (колонка => значение).
     * @return $this Возвращает текущий объект модели.
     */
    public function where(array $conditions): static {
        foreach ($conditions as $column => $value) {
            $this->conditions[] = [
                'column' => $column,
                'operator' => '=',
                'value' => $value,
            ];
        }
        return $this;
    }

    /**
     * Генерирует SQL-условия WHERE.
     *
     * @return string SQL-условия WHERE.
     */
    protected function prepareWhere(): string {
        if (empty($this->conditions)) {
            return '';
        }
        $clauses = [];
        foreach ($this->conditions as $index => $condition) {
            $placeholder = "{$condition['column']}_{$index}";
            $clauses[] = "{$condition['column']} {$condition['operator']} :{$placeholder}";
        }
        return ' WHERE ' . implode(' AND ', $clauses);
    }

    /**
     * Проверяет наличие колонки в таблице.
     *
     * @param string $column Имя колонки.
     * @return bool Возвращает true, если колонка существует.
     */
    protected function hasColumn(string $column): bool {
        $query = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE '{$column}'");
        return (bool) $query->fetch();
    }

    /**
     * Возвращает количество записей.
     *
     * @return int Количество записей.
     */
    public function count(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}" . $this->prepareWhere();
        $query = $this->db->prepare($sql);
        $params = [];
        foreach ($this->conditions as $index => $condition) {
            $params["{$condition['column']}_{$index}"] = $condition['value'];
        }
        $query->execute($params);
        return (int) $query->fetchColumn();
    }

    /**
     * Ограничивает количество возвращаемых записей.
     *
     * @param int $limit Количество записей.
     * @return static Возвращает текущий объект модели.
     */
    public function limit(int $limit): static {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Устанавливает порядок сортировки.
     *
     * @param string $column Поле для сортировки.
     * @param string $direction Направление сортировки ('ASC' или 'DESC').
     * @return static Возвращает текущий объект модели.
     * @throws InvalidArgumentException Если колонка не существует.
     */
    public function orderBy(string $column, string $direction = 'ASC'): static {
        if (!$this->hasColumn($column)) {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }
        $this->orderBy = $column;
        $this->orderDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }

    /**
     * Возвращает первую запись.
     *
     * @return array<string, mixed>|null Первая запись или null, если данных нет.
     */
    public function first(): ?array {
        return $this->limit(1)->all()[0] ?? null;
    }

    /**
     * Возвращает последнюю запись.
     *
     * @param string $column Поле для сортировки (по умолчанию 'id').
     * @return array<string, mixed>|null Последняя запись или null, если данных нет.
     */
    public function last(string $column = 'id'): ?array {
        return $this->orderBy($column, 'DESC')->limit(1)->all()[0] ?? null;
    }

    /**
     * Извлекает записи из таблицы с учётом фильтров.
     *
     * @return array<array<string, mixed>> Массив записей.
     */
    public function all(): array {
        $sql = "SELECT * FROM {$this->table}";
        $sql .= $this->prepareWhere();
        $sql .= $this->orderBy ? " ORDER BY {$this->orderBy}" : '';
        $sql .= $this->orderDirection ? " {$this->orderDirection}" : '';
        $sql .= $this->limit ? " LIMIT {$this->limit}" : '';
        $query = $this->db->prepare($sql);
        $params = [];
        foreach ($this->conditions as $index => $condition) {
            $params["{$condition['column']}_{$index}"] = $condition['value'];
        }
        $query->execute($params);
        return $query->fetchAll();
    }

    /**
     * Находит запись по ID.
     *
     * @param int|string $id Значение первичного ключа.
     * @return array<string, mixed>|null Найденная запись или null, если запись отсутствует.
     */
    public function find(int|string $id): ?array {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $query->execute(['id' => $id]);
        return $query->fetch() ?: null;
    }

    /**
     * Выполняет вставку или обновление данных.
     *
     * @param array<string, mixed> $data Данные для сохранения.
     * @return bool Успешность операции.
     */
    public function save(array $data): bool {
        if (isset($data['id'])) {
            $columns = array_keys($data);
            $sets = implode(', ', array_map(fn($col) => "{$col} = :{$col}", $columns));
            $query = $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = :id");
        } else {
            $columns = array_keys($data);
            $placeholders = implode(', ', array_map(fn($col) => ":{$col}", $columns));
            $query = $this->db->prepare("INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})");
        }
        return $query->execute($data);
    }

    /**
     * Удаляет запись по ID.
     *
     * @param int $id Значение первичного ключа.
     * @return bool Успешность удаления.
     */
    public function delete(int $id): bool {
        $query = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $query->execute(['id' => $id]);
    }
}
