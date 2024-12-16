<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

use Sumish\Controller;
use Sumish\Exception\NotFoundException;

/**
 * Класс Router отвечает за маршрутизацию запросов.
 *
 * @package Sumish
 */
class Router {
    /**
     * Массив маршрутов.
     *
     * @var array<string, array<string, string>>
     */
    private array $routes = [];

    /**
     * Конструктор класса Router.
     *
     * @param Container $container Контейнер зависимостей.
     */
    function __construct(private Container $container) {
    }

    /**
     * Добавляет маршрут в список маршрутов.
     *
     * @param string $uri URI маршрута.
     * @param array{controller: string, action: string} $target Массив с информацией о маршруте.
     * @return $this Возвращает текущий экземпляр Router.
     * @throws \InvalidArgumentException Если URI или цель маршрута некорректны.
     */
    public function add(string $uri, array $target): self
    {
        if (empty($uri) || empty($target['controller']) || empty($target['action'])) {
            throw new \InvalidArgumentException("Invalid uri or target provided.");
        }

        $this->routes[$uri] = [
            'controller' => $target['controller'],
            'action' => $target['action'],
        ];

        return $this;
    }

    /**
     * Добавляет несколько маршрутов.
     *
     * @param array<string, array{controller: string, action: string}> $routes Массив маршрутов.
     * @return $this Возвращает текущий экземпляр Router.
     */
    public function push(array $routes): self
    {
        foreach ($routes as $uri => $target) {
            $this->add($uri, $target);
        }

        return $this;
    }

    /**
     * Получает маршрут по его имени или все маршруты.
     *
     * @param string|null $uri URI маршрута.
     * @return array<string, array<string, string>>|array<string, string>|null
     * Возвращает массив всех маршрутов, массив конкретного маршрута или null, если маршрут не найден.
     */
    public function get(?string $uri = null): array|null
    {
        if (is_null($uri)) {
            return $this->routes; // Возвращает все маршруты
        }

        return $this->routes[$uri] ?? null; // Возвращает маршрут или null
    }

    /**
     * Находит маршрут, соответствующий URI запроса.
     *
     * @param string $requestUri URI запроса.
     * @return array{controller: string, action: string, parameters: array<string, string>}
     * @throws NotFoundException Если маршрут не найден.
     */
    public function match(string $requestUri): array
    {
        if (isset($this->routes[$requestUri])) {
            return [
                'controller' => $this->routes[$requestUri]['controller'],
                'action' => $this->routes[$requestUri]['action'],
                'parameters' => [],
            ];
        }

        foreach ($this->routes as $uri => $target) {
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $uri);
            $pattern = "#^{$pattern}$#";
            if (preg_match($pattern, $requestUri, $matches)) {
                return [
                    'controller' => $target['controller'],
                    'action' => $target['action'],
                    'parameters' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY),
                ];
            }
        }
    
        throw new NotFoundException("Route not found for URI: {$requestUri}");
    }

    /**
     * Создаёт экземпляр контроллера на основе маршрута.
     *
     * @param array{
     *     controller: string,
     *     action: string,
     *     parameters?: array<string, string>
     * } $match Массив маршрута.
     * @return Controller Экземпляр контроллера.
     * @throws \InvalidArgumentException Если отсутствует ключ 'controller' или 'action'.
     * @throws \RuntimeException Если файл контроллера или класс не найден.
     */
    public function resolveController(array $match): Controller
    {
        if (!isset($match['controller'])) {
            throw new \InvalidArgumentException("No controller defined in route.");
        }

        if (!isset($match['action'])) {
            throw new \InvalidArgumentException("No action defined in route.");
        }

        $controller = $match['controller'];
        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class not found: {$controllerClass}");
        }

        $controllerInstance = new $controllerClass($this->container);

        if (!$controllerInstance instanceof Controller) {
            throw new \RuntimeException("Controller does not extend the base Controller class.");
        }

        $controllerInstance->match = $match;
        return $controllerInstance;
    }

    /**
     * Вызывает действие контроллера с передачей параметров.
     *
     * @param Controller $controller Экземпляр контроллера.
     * @return mixed Результат выполнения метода контроллера.
     * @throws \RuntimeException Если метод действия не найден в контроллере.
     */
    public function dispatch(Controller $controller): mixed {
        $action = $controller->match['action'];
        $parameters = $controller->match['parameters'] ?? [];
    
        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action method '{$action}' not found in controller " . get_class($controller));
        }

        $reflection = new \ReflectionMethod($controller, $action);
        $methodParameters = $reflection->getParameters();
    
        $parameters = array_map(function ($parameter) use ($parameters) {
            $name = $parameter->getName();
            return $parameters[$name] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        }, $methodParameters);

        return call_user_func_array([$controller, $action], $parameters);
    }
}
