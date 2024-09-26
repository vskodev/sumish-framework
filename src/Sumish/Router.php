<?php

namespace Sumish;

class Router {
    public $routes = [];
    protected $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    public function getURI() {
        if (is_null($_REQUEST['route'])) {
            $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        } else {
            $route = $_REQUEST['route'] ?? '/';
        }

        return '/' . trim($route, '/');
    }

    public function configureRoutes(array $routes = []) {
        foreach (glob(DIR_APP .'/*/config.php') as $config) {
            $config = include($config);
            if ($config['routes']) {
                $routes = array_merge($routes, $config['routes']);
            }
        }

        return $routes;
    }

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

    public function push(array $routes) {
        foreach ($routes as $route=>$target) {
            $this->add($route, $target);
        }

        return $this;
    }

    public function load(array $routes = []) {
        $uri = $this->getURI();
        $routes = $this->configureRoutes($routes);

        $this->push($routes)->match($uri);
    }

    public function get($route = null) {
        if (is_null($route)) {
            return $this->routes;
        } else {
            return [$route, $this->routes[$route]];
        }
    }

    public function match($uri) {
        $params = $parts = [];
        $app = new class {};

        foreach ($this->routes as $route=>$target) {
            if (preg_match("#^${route}$#i", $uri, $params)) {
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

    public function execute($app, $register = true) {
        $ownController = ucfirst($app->name) . ucfirst(str_replace('action',
                            '', strtolower($app->action))) . 'Controller';

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
