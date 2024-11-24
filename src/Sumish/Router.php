<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use Sumish\Exceptions\NotFoundException;

/**
 * Класс Router отвечает за маршрутизацию запросов.
 *
 * Он управляет загрузкой маршрутов, сопоставлением URI с контроллерами и
 * обработкой входящих запросов.
 *
 * @package Sumish
 */
class Router {
    /**
     * Массив маршрутов.
     *
     * @var array
     */
    private $routes = [];

    function __construct(private Container $container) {
        $this->container = $container;
    }

    /**
     * Конфигурирует маршруты, загружая их из файлов конфигурации.
     *
     * @param array $routes Массив маршрутов для конфигурации.
     * @return array Возвращает объединённый массив маршрутов.
     */
    public function configureRoutes(array $routes = []) {
        foreach (glob(getcwd() . '/app/*/config.php') as $config) {
            $config = include($config);
            if ($config['routes']) {
                $routes = array_merge($routes, $config['routes']);
            }
        }

        return $routes;
    }

    /**
     * Получает маршрут по его имени.
     *
     * @param string|null $route Имя маршрута.
     * @return array Возвращает массив маршрута или null, если не найдено.
     */
    public function get($route = null): array {
        if (is_null($route)) {
            return $this->routes;
        } else {
            return [$route, $this->routes[$route]];
        }
    }

    /**
     * Добавляет маршрут в список маршрутов.
     *
     * @param string|array $route Маршрут или массив маршрутов.
     * @param string|null $target Целевая точка назначения для маршрута.
     * @return $this Возвращает текущий экземпляр Router.
     */
    public function add($route, $target = null) {
        if (empty($route)) {
            return false;
        }

        if (is_array($route) && is_null($target)) {
            $route = $route[0];
            $target = $route[1];
        }

        $this->routes[$route] = $target;

        return $this;
    }

    /**
     * Добавляет массив маршрутов.
     *
     * @param array $routes Массив маршрутов.
     * @return $this Возвращает текущий экземпляр Router.
     */
    public function push(array $routes) {
        // $routes = $this->configureRoutes($routes);
        
        foreach ($routes as $route => $target) {
            $this->add($route, $target);
        }

        return $this;
    }

    public function match(string $uri): array {
        foreach ($this->routes as $route => $target) {
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route);
            $pattern = "#^{$pattern}$#";
    
            if ($route === $uri || preg_match($pattern, $uri, $matches)) {
                return [
                    'controller' => $target['controller'],
                    'action' => $target['action'],
                    'parameters' => isset($matches) ? array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY) : [],
                ];
            }
        }

        throw new NotFoundException("Route not found for URI: {$uri}");
    }

    /**
     * Находит и создаёт экземпляр контроллера на основе маршрута.
     *
     * @param array $match Данные маршрута: имя контроллера и действие.
     * @return object Экземпляр контроллера.
     * @throws Exception Если файл контроллера или класс не найден.
     */
    public function resolveController(array $match): object {
        if (!isset($match['controller'])) {
            throw new \Exception("No controller defined in route.");
        }

        if (!isset($match['action'])) {
            throw new \Exception("No action defined in route.");
        }

        $controller = $match['controller'];
        $controllerClass = "App\\Controllers\\{$controller}";
        $controllerPath = getcwd() . "/app/controllers/{$controller}.php";

        if (!is_file($controllerPath)) {
            throw new \Exception("Controller file not found: {$controllerPath}");
        }

        require_once $controllerPath;

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller class not found: {$controllerClass}");
        }

        $controllerInstance = new $controllerClass($this->container);
        $controllerInstance->match = $match;

        return $controllerInstance;
    }

    /**
     * Выполняет указанное действие контроллера.
     *
     * Метод вызывает действие контроллера, передавая параметры маршрута.
     *
     * @param object $controller Экземпляр контроллера.
     * @return mixed Результат выполнения действия контроллера.
     * @throws Exception Если действие не определено или отсутствует.
     */
    public function dispatch(object $controller) {
        $action = $controller->match['action'];
        $parameters = $controller->match['parameters'] ?? [];

        if (!method_exists($controller, $action)) {
            throw new \Exception("Action method '{$action}' not found in controller " . get_class($controller));
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
