<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Loader отвечает за динамическую загрузку моделей и библиотек.
 *
 * Этот класс предоставляет методы для удобной загрузки
 * необходимых компонентов с поддержкой кэширования.
 *
 * @package Sumish
 */
class Loader {
    /**
     * Путь для загрузки текущего ресурса.
     * @var string
     */
    private string $path;

    /**
     * Кэш загруженных экземпляров.
     * @var array
     */
    private array $cache = [];

    /**
     * Конструктор класса Loader.
     *
     * Инициализирует объект загрузчика с контейнером зависимостей.
     *
     * @param Container $container Экземпляр контейнера для управления зависимостями.
     */
    function __construct(private Container $container) {
        $this->container = $container;
    }

    /**
     * Загружает модель по имени.
     *
     * @param string $name Имя модели (например, 'User').
     * @return object Экземпляр загруженной модели.
     * @throws Exception Если модель не найдена.
     */
    public function model(string $name): object {
        $this->path = getcwd() . '/app/models';
        return $this->resolve($name);
    }

    /**
     * Загружает библиотеку по имени.
     *
     * @param string $name Имя библиотеки (например, 'PdfGenerator').
     * @return object Экземпляр загруженной библиотеки.
     * @throws Exception Если библиотека не найдена.
     */
    public function library(string $name): object {
        $this->path = getcwd() . '/app/libraries';
        return $this->resolve($name);
    }

    /**
     * Общий метод для загрузки и создания экземпляра класса.
     *
     * @param string $name Имя класса.
     * @return object Экземпляр загруженного класса.
     * @throws Exception Если файл или класс не найден.
     */
    private function resolve(string $name): object {
        $resourcePath = "{$this->path}/{$name}.php";

        $id = "{$this->path}.{$name}";
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        if (!file_exists($resourcePath)) {
            throw new \Exception("Resource file {$resourcePath} not found.");
        }

        require_once $resourcePath;

        $resourceName = "App\\" . ucfirst(basename($this->path)) . "\\{$name}";
        if (!class_exists($resourceName)) {
            throw new \Exception("Resource class {$resourceName} not found.");
        }

        $this->cache[$id] = new $resourceName($this->container->config);
        return $this->cache[$id];
    }
}
