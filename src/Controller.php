<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

/**
 * Базовый класс контроллеров.
 *
 * Предоставляет доступ к зависимостям и базовые методы для обработки запросов.
 *
 * @package Sumish
 */
abstract class Controller {
    /**
     * Данные маршрута.
     *
     * @var array<string, mixed>
     */
    public array $match = [];

    /**
     * Конструктор класса Controller.
     *
     * @param Container $container Контейнер зависимостей.
     */
    public function __construct(private Container $container) {
        $this->container = $container;
    }

    /**
     * Получает компонент из контейнера по идентификатору.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает экземпляр компонента из контейнера.
     */
    public function __get(string $id): mixed {
        return $this->container->get($id);
    }

    /**
     * Возвращает контейнер зависимостей.
     *
     * @return Container Контейнер зависимостей.
     */
    public function container(): Container {
        return $this->container;
    }
}
