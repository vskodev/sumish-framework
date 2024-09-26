<?php

namespace Sumish;

class Loader {
    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function model($model) {
        if (strrpos($model, '/')) {
            $parts = explode('/', $model);
            $path = DIR_APP . '/' . $parts[0];
            $model = $parts[1];
        } else {
            //$app->path = DIR_APP . '/' . $app->name;
            $path = $this->container->app->path;
        }

        $class = ucfirst($model) . 'Model';
        $file = $path . '/model.php';

        if (is_file($file)) {
            require_once($file);
        } else {
            $file = $path . '/models/' . $class . '.php';

            if (is_file($file)) {
                require_once($file);
            }
        }

        if (class_exists($class, false)) {
            return new $class($this->container->config);
            //return new $class;
        }

        return false;
    }

    public function controller($controller) {
        $app = new class {};
        $app->action = 'indexAction';
        $app->params = [];

        if (is_string($controller)) {
            $parts = explode('/', $controller);

            $app->name = $parts[0];
            $app->controller = ucfirst($app->name);
            $app->path = DIR_APP . '/' . $app->name;

            if (count($parts) > 1) {
                $app->controller .= ucfirst($parts[1]);
                $app->action = $parts[1] . 'Action';
            }

            $app->controller .= 'Controller';
        }

        return $this->container->route->execute($app, false);
    }

    public function lib($name) {
        return $this->library($name);
    }

    public function library($library) {
        $class = ucfirst($library) . 'Library';
        $path = dirname($this->container->app->path);
        $scanPath = $path . '/*/library/' . $library . '/library.php';
        $pathFiles[] = dirname(dirname(__DIR__)) . '/library/' . $library . '/library.php';

        foreach (glob($scanPath) as $filename) {
            $pathFiles[] = $filename;
        }

        foreach ($pathFiles as $file) {
            if (is_file($file)) {
                require_once($file);
                if (method_exists($class, 'init')) {
                    (new $class())->init();
                    break;
                }
            }
        }

        return false;
    }

    public function libraries($libraries) {
        if (is_array($libraries)) {
            foreach ($libraries as $name) {
                $this->lib($name);
            }
        }
    }

    public function routes($routes) {
        $this->container->route->load($routes);
    }
}
