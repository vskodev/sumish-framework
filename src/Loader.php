<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

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
     * Ресурс Models / Libraries.
     *
     * @var string
     */
    private string $resource;

    /**
     * Кешированные данные.
     *
     * @var array<string, object>
     */
    private array $cache = [];

    /**
     * Конструктор класса Loader.
     *
     * Инициализирует объект загрузчика с контейнером зависимостей.
     *
     * @param Container $container Контейнер зависимостей.
     */
    function __construct(private Container $container) {
        $this->container = $container;
    }

    /**
     * Загружает модель по имени.
     *
     * @param string $name Имя модели (например, 'User').
     * @return object Экземпляр загруженной модели.
     * @throws \InvalidArgumentException Если модель не найдена.
     */
    public function model(string $name): object {
        $this->resource = 'Models';
        return $this->load($name . 'Model');
    }

    /**
     * Загружает библиотеку по имени.
     *
     * @param string $name Имя библиотеки (например, 'PdfGenerator').
     * @return object Экземпляр загруженной библиотеки.
     * @throws \InvalidArgumentException Если библиотека не найдена.
     */
    public function library(string $name): object {
        $this->resource = 'Libraries';
        return $this->load($name);
    }

    /**
     * Общий метод для загрузки и создания экземпляра класса.
     *
     * @param string $name Имя класса.
     * @return object Экземпляр загруженного класса.
     * @throws \InvalidArgumentException Если файл или класс не найден.
     */
    private function load(string $name): object {
        $resource = $this->resource;
        $id = "{$resource}\\{$name}";
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        $resourceClass = "App\\{$resource}\\{$name}";
        if (!class_exists($resourceClass)) {
            throw new \InvalidArgumentException("Resource class not found: {$resourceClass}");
        }

        $config = $this->container->get('config');
        $this->cache[$id] = new $resourceClass($config);
        return $this->cache[$id];
    }
}
