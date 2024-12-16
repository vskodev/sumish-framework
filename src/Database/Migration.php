<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

namespace Sumish\Database;

/**
 * Базовый класс для миграций.
 *
 * Определяет методы для создания и отката миграций.
 *
 * @package Sumish\Database
 */
abstract class Migration {
    /**
     * Применение миграции.
     */
    abstract public function up(): void;

    /**
     * Откат миграции.
     */
    abstract public function down(): void;

    /**
     * Cоздание таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param array<string, string> $columns Массив колонок (название => определение).
     * @param array<array{type: string, columns: string, references?: string, on?: string, onDelete?: string, onUpdate?: string}> $constraints Массив ограничений (например, FOREIGN KEY, UNIQUE, INDEX).
     * @return void
     */
    protected function createTable(string $tableName, array $columns, array $constraints = []): void {
        $columnsSql = [];
        foreach ($columns as $name => $definition) {
            $columnsSql[] = "`$name` $definition";
        }

        $constraintsSql = [];
        foreach ($constraints as $constraint) {
            if (isset($constraint['type'], $constraint['columns'])) {
                if ($constraint['type'] === 'FOREIGN KEY') {
                    $constraintsSql[] = "FOREIGN KEY (`{$constraint['columns']}`) REFERENCES `{$constraint['references']}` (`{$constraint['on']}`) "
                        . (isset($constraint['onDelete']) ? "ON DELETE {$constraint['onDelete']}" : "")
                        . (isset($constraint['onUpdate']) ? "ON UPDATE {$constraint['onUpdate']}" : "");
                } elseif (in_array($constraint['type'], ['UNIQUE', 'INDEX'], true)) {
                    $constraintsSql[] = "{$constraint['type']} (`{$constraint['columns']}`)";
                }
            }
        }

        $sql = "CREATE TABLE `$tableName` (" . implode(", ", array_merge($columnsSql, $constraintsSql)) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->execute($sql);
        echo "Table `$tableName` created.\n";
    }

    /**
     * Удаление таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @return void
     */
    protected function dropTable(string $tableName): void {
        $sql = "DROP TABLE IF EXISTS `$tableName`";
        $this->execute($sql);
        echo "Table `$tableName` dropped.\n";
    }

    /**
     * Добавляет новую колонку в таблицу.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки.
     * @param string $definition Определение колонки (тип и дополнительные параметры).
     * @return void
     */
    protected function addColumn(string $tableName, string $columnName, string $definition): void {
        $sql = "ALTER TABLE `$tableName` ADD `$columnName` $definition";
        $this->execute($sql);
        echo "Column `$columnName` added to `$tableName`.\n";
    }

    /**
     * Удаляет колонку из таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки.
     * @return void
     */
    protected function dropColumn(string $tableName, string $columnName): void {
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        $this->execute($sql);
        echo "Column `$columnName` dropped from `$tableName`.\n";
    }

    /**
     * Добавляет индекс к колонке таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки.
     * @param string|null $indexName Имя индекса (по умолчанию генерируется на основе имени таблицы и колонки).
     * @return void
     */
    protected function addIndex(string $tableName, string $columnName, ?string $indexName = null): void {
        $indexName = $indexName ?? "{$tableName}_{$columnName}_index";
        $sql = "CREATE INDEX `$indexName` ON `$tableName` (`$columnName`)";
        $this->execute($sql);
        echo "Index `$indexName` created on `$tableName`.\n";
    }

    /**
     * Добавляет уникальный индекс к колонке таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки.
     * @param string|null $indexName Имя индекса (по умолчанию генерируется на основе имени таблицы и колонки).
     * @return void
     */
    protected function addUniqueIndex(string $tableName, string $columnName, ?string $indexName = null): void {
        $indexName = $indexName ?? "{$tableName}_{$columnName}_unique";
        $sql = "CREATE UNIQUE INDEX `$indexName` ON `$tableName` (`$columnName`)";
        $this->execute($sql);
        echo "Unique index `$indexName` created on `$tableName`.\n";
    }

    /**
     * Удаляет индекс из таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $indexName Имя индекса.
     * @return void
     */
    protected function dropIndex(string $tableName, string $indexName): void {
        $sql = "DROP INDEX `$indexName` ON `$tableName`";
        $this->execute($sql);
        echo "Index `$indexName` dropped from `$tableName`.\n";
    }

    /**
     * Добавляет внешний ключ к колонке таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки.
     * @param string $referencedTable Таблица, к которой ссылается внешний ключ.
     * @param string $referencedColumn Колонка, к которой ссылается внешний ключ.
     * @param string $onDelete Действие при удалении (по умолчанию 'RESTRICT').
     * @param string $onUpdate Действие при обновлении (по умолчанию 'RESTRICT').
     * @return void
     */
    protected function addForeignKey(
        string $tableName,
        string $columnName,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete = 'RESTRICT',
        string $onUpdate = 'RESTRICT'
    ): void {
        $constraintName = "{$tableName}_{$columnName}_fk";
        $sql = "ALTER TABLE `$tableName` 
                ADD CONSTRAINT `$constraintName` 
                FOREIGN KEY (`$columnName`) 
                REFERENCES `$referencedTable` (`$referencedColumn`) 
                ON DELETE $onDelete 
                ON UPDATE $onUpdate";
        $this->execute($sql);
        echo "Foreign key `$constraintName` added to `$tableName`.\n";
    }

    /**
     * Удаляет внешний ключ из таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @param string $columnName Имя колонки, к которой привязан внешний ключ.
     * @return void
     */
    protected function dropForeignKey(string $tableName, string $columnName): void {
        $constraintName = "{$tableName}_{$columnName}_fk";
        $sql = "ALTER TABLE `$tableName` DROP FOREIGN KEY `$constraintName`";
        $this->execute($sql);
        echo "Foreign key `$constraintName` dropped from `$tableName`.\n";
    }

    /**
     * Удаляет первичный ключ из таблицы.
     *
     * @param string $tableName Имя таблицы.
     * @return void
     */
    protected function dropPrimaryKey(string $tableName): void {
        $sql = "ALTER TABLE `$tableName` DROP PRIMARY KEY";
        $this->execute($sql);
        echo "Primary key dropped from `$tableName`.\n";
    }

    /**
     * Выполняет SQL-запрос.
     *
     * @param string $sql SQL-запрос, который необходимо выполнить.
     * @return void
     */
    protected function execute(string $sql): void {
        $config = self::loadConfig();
        $db = Connection::open($config['db']);
        $db->exec($sql);
    }

    /**
     * Загружает конфигурацию.
     *
     * @return array<string, mixed> Конфигурация в виде массива.
     */
    private static function loadConfig(): array {
        return is_file($config = getcwd() . '/config.php') ? (array)(require $config) : [];
    }
}
