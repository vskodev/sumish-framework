<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Router отвечает за маршрутизацию запросов в приложении.
 *
 * Он управляет загрузкой маршрутов, сопоставлением URI с контроллерами и
 * обработкой входящих запросов.
 *
 * @package Sumish
 */
class Router {
    /**
     * Контейнер зависимостей.
     *
     * @var \Sumish\Container
     */
    private Container $container;

    /**
     * Массив маршрутов.
     *
     * @var array
     */
    private $routes = [];

    /**
     * Конструктор класса Router.
     *
     * @param Container $container Контейнер зависимостей.
     */
    function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Получает URI из запроса.
     *
     * Проверяет наличие параметра 'route' в GET-запросе и извлекает URI из
     * текущего запроса, обрабатывая базовый путь.
     *
     * @return string Возвращает обработанный URI.
     */
    public function getURI() {
        if (isset($_GET['route']) && !empty($_GET['route'])) {
            $route = $_GET['route'];
        } else {
            $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if (strpos($route, $basePath) === 0) {
                $route = substr($route, strlen($basePath));
            }
        }
    
        $route = '/' . trim($route, '/');
        return $route ?: '/';
    }

    /**
     * Конфигурирует маршруты, загружая их из файлов конфигурации.
     *
     * @param array $routes Массив маршрутов для конфигурации.
     * @return array Возвращает объединённый массив маршрутов.
     */
    public function configureRoutes(array $routes = []) {
        foreach (glob(DIR_APP .'/*/config.php') as $config) {
            $config = include($config);
            if ($config['routes']) {
                $routes = array_merge($routes, $config['routes']);
            }
        }

        return $routes;
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
            $target = $route[1];
            $route = $route[0];
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
        foreach ($routes as $route => $target) {
            $this->add($route, $target);
        }

        return $this;
    }

    /**
     * Загружает маршруты и выполняет их сопоставление с текущим URI.
     *
     * @param array $routes Массив маршрутов для загрузки.
     */
    public function load(array $routes = []) {
        $uri = $this->getURI();
        $routes = $this->configureRoutes($routes);

        $this->push($routes)->match($uri);
    }

    /**
     * Получает маршрут по его имени.
     *
     * @param string|null $route Имя маршрута.
     * @return array|null Возвращает массив маршрута или null, если не найдено.
     */
    public function get($route = null) {
        if (is_null($route)) {
            return $this->routes;
        } else {
            return [$route, $this->routes[$route]];
        }
    }

    /**
     * Сопоставляет текущий URI с зарегистрированными маршрутами.
     *
     * @param string $uri URI для сопоставления.
     * @return bool Возвращает true, если сопоставление успешно, иначе false.
     */
    public function match($uri) {
        $params = $parts = [];
        $app = new class {
            public $name;
            public $controller;
            public $action;
            public $params;
            public $path;
        };

        foreach ($this->routes as $route => $target) {
            if (preg_match("#^{$route}$#i", $uri, $params)) {
                $parts = explode('/', $target);

                $app->name = strtolower($parts[0]);
                $app->controller = ucfirst($parts[0]) . 'Controller';
                $app->action = (count($parts) == 2) ? $parts[1] . 'Action'
                    : ((count($parts) == 1) ? 'indexAction' : null);

                if (is_null($app->action)) {
                    trigger_error('No match action');
                }

                $app->params = $params;
                $app->path = DIR_APP . '/' . $app->name;
                break;
            }
        }

        return $this->execute($app);
    }

    /**
     * Выполняет контроллер с заданным приложением.
     *
     * @param object $app Объект приложения с данными контроллера и действия.
     * @param bool $register Указывает, нужно ли регистрировать контроллер.
     * @return bool Возвращает true, если выполнение успешно, иначе false.
     */
    public function execute($app, $register = true) {
        $ownController = ucfirst($app->name) . ucfirst(str_replace('action', '', strtolower($app->action))) . 'Controller';

        $pathFiles = [
            '/controller.php',
            '/controllers/' . $app->controller . '.php',
            '/controllers/' . $ownController . '.php'
        ];

        foreach ($pathFiles as $file) {
            $file = $app->path . $file;

            if (is_file($file)) {
                require_once($file);

                if (class_exists($app->controller, false)) {
                    if (method_exists($app->controller, $app->action)) {
                        break;
                    }
                }

                if (class_exists($ownController, false)) {
                    $app->controller = $ownController;
                    $app->action = 'indexAction';
                    break;
                }
            }
        }

        if (method_exists($app->controller, $app->action)) {
            if ($register) {
                $this->container->register('app', $app);
                $this->container->register('controller', $app->controller);
                return true;
            } else {
                return $this->container->build($app->controller);
            }
        } else {
            return false;
        }
    }
}
