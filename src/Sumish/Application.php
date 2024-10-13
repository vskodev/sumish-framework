<?php

namespace Sumish;

define('DIR_ROOT', getcwd());
define('DIR_APP', DIR_ROOT . '/app');
define('DEBUG', true);

ini_set('display_errors', DEBUG);
ini_set('track_errors', DEBUG);

class Application {
    public $container;

    public function __construct(array $config = []) {
        set_error_handler([&$this, 'errorHandler']);

        $this->container = Container::create(array_merge(self::componentsDefault(), (array)$config['components']));

        $this->config($config);

        $this->load->routes($this->config['routes']);
        $this->load->libraries($this->config['libraries']);

        $this->response->addHeaders($this->config['headers']);
        $this->response->setCompression($this->config['compression']);
    }

    public function __get($id) {
        return $this->container[$id];
    }

    public function __set($id, $component) {
        $this->container[$id] = $component;
    }

    public function __call($id, $parameters) {
        return $this->container->resolveCallback($id, $parameters);
    }

    public function run() {
        if ($this->controller) {
            $this->controller->dispatch();
        } else {
            $this->error(404);
        }

        $this->response->init();
        $this->document->init();
    }

    public static function componentsDefault() {
        return [
            'session' => \Sumish\Session::class,
            'request' => \Sumish\Request::class,
            'response' => \Sumish\Response::class,
            'document' => \Sumish\Document::class,
            'route' => \Sumish\Router::class,
            'load' => \Sumish\Loader::class,
            'view' => \Sumish\View::class,
        ];
    }

    public static function configDefault() {
        return [
            'routes' => [
                '/' => 'home',
            ],
            'headers' => [
                1000 => 'Content-Type: text/html; charset=utf-8',
            ],
            'components' => self::componentsDefault(),
            'db' => [
                'driver' => 'mysql',
                'host' => '',
                'database' => '',
                'user' => '',
                'password' => '',
                'charset' => 'utf8',
            ],
            'compression' => 0,
            'mode' => 'pro'
        ];
    }

    public function config($data, $name = null, $value = null) {
        $config = $this->config;

        if (is_array($data) && is_null($name) && is_null($value)) {
            $config = $data;
        } elseif (is_string($data) && !is_null($name) && is_null($value)) {
            $config[$data] = $name;
        } elseif (is_string($data) && is_string($name) && is_string($value)) {
            $config[$data][$name] = $value;
        } elseif (is_string($data) && is_null($name) && is_null($value)) {
            return $config[$data];
        } else {
            return false;
        }

        $config = array_replace_recursive(
            self::configDefault(), $config
        );

        $this->container->register('config', $config);

        return true;
    }

    public function mode($mode, $callback) {
        if ($mode == $this->config['mode']) {
            call_user_func($callback);
        }
    }

    public function error($status) {
        $errorText = 'Error ';

        switch ($status) {
            case 404: { $errorText .= '404 Not Found'; } break;
        }

        $this->response->setOutput($errorText);
    }

    public function errorHandler($level, $message, $file, $line, $context) {
        if($level === E_USER_ERROR || $level === E_USER_WARNING || $level === E_USER_NOTICE) {
            $context = (false) ? ' <pre style="color: #555; background: #eee; padding: 15px">*** Context data:<br /><br />' . print_r($context, true) . '</pre>' : '';
            $message .= defined('DEBUG') ? ' in ' . $file . ' on line ' .$line . $context: '';

            die('<strong>System Error:</strong> ' . $message);
        }
    }
}
