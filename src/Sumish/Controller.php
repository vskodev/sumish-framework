<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Абстрактный класс контроллера.
 *
 * Этот класс служит основой для всех контроллеров в приложении, 
 * обеспечивая доступ к контейнеру зависимостей и методам 
 * для обработки действий. Он предназначен для расширения другими 
 * контроллерами, которые будут реализовывать свои собственные 
 * действия и логику.
 *
 * @package Sumish
 */
abstract class Controller {
    /**
     * Контейнер зависимостей.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Данные маршрута.
     *
     * @var array
     */
    public array $match = [];

    /**
     * Конструктор класса Controller.
     *
     * @param Container $container Контейнер зависимостей, который будет использоваться контроллером.
     */
    function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Получает компонент из контейнера по идентификатору.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает экземпляр компонента из контейнера.
     */
    public function __get($id) {
        return $this->container->get($id);
    }
}
