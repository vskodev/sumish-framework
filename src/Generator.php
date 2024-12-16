<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

use Composer\Script\Event;
use Sumish\Database\Connection;
use Sumish\Database\Migration;

/**
 * Класс Generator предоставляет команды для генерации компонентов приложения.
 *
 * Управляет созданием контроллеров, моделей, представлений, миграций
 * и других составляющих приложения через консольные команды.
 *
 * @package Sumish
 */
class Generator {
    /**
     * Генерирует контроллер.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function createController(Event $event): void {
        $args = self::parseArgs();
        $name = $args['name'] ?? null;

        if (!$name) {
            echo "Usage: composer generate:controller [name]\n";
            return;
        }

        $path = "app/Controllers/{$name}Controller.php";
        self::generateFile($path, <<<PHP
<?php

namespace App\Controllers;

use Sumish\Controller;

class {$name}Controller extends Controller {
    public function index() {
        return 'Hello from {$name}Controller';
    }
}
PHP
        );
    }

    /**
     * Генерирует модель.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function createModel(Event $event): void {
        $args = self::parseArgs();
        $name = $args['name'] ?? null;

        if (!$name) {
            echo "Usage: composer generate:model [name]\n";
            return;
        }

        $path = "app/Models/{$name}Model.php";
        $tableName = strtolower($name);

        self::generateFile($path, <<<PHP
<?php

namespace App\Models;

use Sumish\Model\Database;

class {$name}Model extends Model {
    protected \$table = '{$tableName}';
    // Your code here
}
PHP
        );
    }

    /**
     * Генерирует шаблон представления.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function createView(Event $event): void {
        $args = self::parseArgs();
        $name = $args['name'];
        $options = $args['options'];

        if (!$name) {
            echo "Usage: composer generate:view [name] --title=<title> --format=<format>\n";
            return;
        }

        $pathParts = explode('/', $name);
        $fileName = array_pop($pathParts);
        $directory = 'app/templates/' . implode('/', $pathParts);

        $title = $options['title'] ?? ucfirst($fileName);
        $format = strtolower($options['format'] ?? 'twig');

        if (!in_array($format, ['twig', 'php'], true)) {
            echo "Unsupported format: {$format}. Supported formats are 'twig', 'php'.\n";
            return;
        }

        $content = ($format === 'php') 
            ? <<<PHP
<!-- {$fileName}.php -->
<h1>{$title}</h1>
<p>Content goes here...</p>
PHP
            : <<<TWIG
<!-- {$fileName}.twig -->
<h1>{$title}</h1>
<p>Content goes here...</p>
TWIG;

        $filePath = "{$directory}/{$fileName}.{$format}";
        self::generateFile($filePath, $content);
    }

    /**
     * Генерирует миграцию.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function createMigration(Event $event): void {
        $args = self::parseArgs();
        $name = $args['name'] ?? null;

        if (!$name) {
            echo "Usage: composer migrate:new [name]\n";
            return;
        }

        $timestamp = date('YmdHis');
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $fileName = "app/migrations/{$timestamp}_{$name}.php";

        $content = <<<PHP
<?php

namespace App\Migrations;

use Sumish\Database\Migration;

class {$className} extends Migration {
    public function up(): void {
        // TODO: Implement migration logic
    }

    public function down(): void {
        // TODO: Implement rollback logic
    }
}
PHP;

        self::generateFile($fileName, $content);
    }

    /**
     * Применяет миграции.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function applyMigration(Event $event): void {
        $appliedMigrations = self::getAppliedMigrations();
        $migrationFiles = glob('app/migrations/*.php');

        $newMigrations = array_filter($migrationFiles, function ($file) use ($appliedMigrations) {
            return !in_array(basename($file, '.php'), $appliedMigrations, true);
        });

        if (empty($newMigrations)) {
            echo "All migrations are applied.\n";
            return;
        }

        foreach ($newMigrations as $migrationFile) {
            require_once $migrationFile;

            $fileName = basename($migrationFile, '.php');
            $className = preg_replace('/^\d+_/', '', $fileName);
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));
            $fullyQualifiedClassName = "App\\Migrations\\{$className}";

            if (!class_exists($fullyQualifiedClassName)) {
                throw new \Exception("Class \"$fullyQualifiedClassName\" not found");
            }

            $migration = new $fullyQualifiedClassName();

            if (!$migration instanceof Migration) {
                throw new \Exception("Migration \"$fullyQualifiedClassName\" is not a valid migration class");
            }

            echo "Applying migration: {$className}\n";
            $migration->up();
            self::saveMigration($fileName);
            echo "Applied migration: {$className}\n";
        }

        echo "All new migrations have been applied.\n";
    }

    /**
     * Откатывает последние миграции.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function rollbackMigration(Event $event): void {
        $args = self::parseArgs();
        $step = isset($args['options']['step']) ? (int)$args['options']['step'] : 1;
    
        $appliedMigrations = self::getAppliedMigrations();
    
        if (empty($appliedMigrations)) {
            echo "No migrations to rollback.\n";
            return;
        }
    
        $migrationsToRollback = array_reverse(array_slice($appliedMigrations, -$step));
    
        foreach ($migrationsToRollback as $migrationName) {
            $migrationFile = "app/migrations/{$migrationName}.php";
    
            if (!file_exists($migrationFile)) {
                echo "Migration file not found: {$migrationFile}\n";
                continue;
            }
    
            require_once $migrationFile;
    
            $className = preg_replace('/^\d+_/', '', basename($migrationFile, '.php'));
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));
            $fullyQualifiedClassName = "App\\Migrations\\{$className}";
    
            if (!class_exists($fullyQualifiedClassName)) {
                throw new \Exception("Class \"$fullyQualifiedClassName\" not found");
            }
    
            $migration = new $fullyQualifiedClassName();
    
            if (!$migration instanceof Migration) {
                throw new \Exception("Migration \"$fullyQualifiedClassName\" is not a valid migration class");
            }
    
            echo "Rolling back migration: {$className}\n";
            $migration->down();
            self::deleteMigration($migrationName);
            echo "Rolled back migration: {$className}\n";
        }
    
        echo "Rollback complete.\n";
    }

    /**
     * Показывает статус миграций.
     *
     * @param Event $event Событие Composer.
     * @return void
     */
    public static function showMigrationStatus(Event $event): void {
        $appliedMigrations = self::getAppliedMigrations();
        $allMigrationFiles = glob('app/migrations/*.php');
    
        $pendingMigrations = array_diff(
            array_map('basename', $allMigrationFiles),
            array_map(fn($m) => "{$m}.php", $appliedMigrations)
        );
    
        echo "=== Migration Status ===\n";
        echo "Applied Migrations:\n";
        foreach ($appliedMigrations as $migration) {
            echo "  - {$migration}\n";
        }
    
        echo "\nPending Migrations:\n";
        foreach ($pendingMigrations as $migration) {
            echo "  - " . basename($migration, '.php') . "\n";
        }
    
        echo "========================\n";
    }

    /**
     * Сохраняет информацию о миграции.
     *
     * @param string $migration Название миграции.
     * @return void
     */
    private static function saveMigration(string $migration): void {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $stmt->execute(['migration' => $migration]);
    }

    /**
     * Удаляет информацию о миграции.
     *
     * @param string $migration Название миграции.
     * @return void
     */
    private static function deleteMigration(string $migration): void {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
        $stmt->execute(['migration' => $migration]);
    }

    /**
     * Получает применённые миграции.
     *
     * @return array<string> Массив строк с именами миграций.
     */
    private static function getAppliedMigrations(): array {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Парсит опции команды.
     *
     * @return array{name: string|null, options: array<string, string>} Возвращает массив с именем и опциями.
     */
    private static function parseArgs(): array {
        /** @var array<string> $argv */
        $argv = $_SERVER['argv'];
        $rawArgs = array_slice($argv, 2);

        if (empty($rawArgs)) {
            return ['name' => null, 'options' => []];
        }

        $name = $rawArgs[0] ?? null;

        $options = [];
        foreach ($rawArgs as $arg) {
            if (preg_match('/^--([\w-]+)=(.+)$/', $arg, $matches)) {
                $options[$matches[1]] = $matches[2];
            }
        }

        return [
            'name' => $name,
            'options' => $options,
        ];
    }

    /**
     * Создает файл на основе переданных данных.
     *
     * @param string $path Путь к файлу.
     * @param string $content Содержимое файла.
     * @return void
     */
    private static function generateFile(string $path, string $content): void {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            echo "Failed to create directory: {$directory}\n";
            return;
        }

        if (file_exists($path)) {
            echo "File {$path} already exists. Overwrite? [y/N]: ";
            $response = trim(fgets(fopen('php://stdin', 'r')));
            if (strtolower($response) !== 'y') {
                echo "Skipped {$path}\n";
                return;
            }
        }

        file_put_contents($path, $content);
        echo "Created " . basename($path) . " in {$path}\n";
    }

    /**
     * Возвращает PDO-соединение.
     *
     * @return \PDO
     */
    private static function getConnection(): \PDO {
        $config = self::loadConfig();
        return Connection::open($config['db']);
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
