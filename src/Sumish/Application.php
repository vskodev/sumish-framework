<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use Sumish\Exceptions\HandlerException;

/**
 * Главный класс для инициализации и управления приложением.
 *
 * Этот класс инициализирует контейнер зависимостей, загружает маршруты, библиотеки, 
 * а также управляет ошибками и отвечает за запуск контроллеров.
 *
 * @package Sumish
 */
class Application {
    /**
     * Контейнер зависимостей.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Конфигурация приложения.
     *
     * @var array
     */
    private array $config = [];

    /**
     * Конструктор класса Application.
     *
     * @param array $config Массив конфигурации приложения.
     */
    public function __construct(array $config = []) {
        $this->configure($config);
        $this->container = Container::create($this->config['components']);
        $this->container->register('config', $this->config);
        // $this->container->load->libraries($this->config['libraries'] ?? []);
    }

    /**
     * Конфигурирует приложение, объединяя значения по умолчанию.
     *
     * @param array $config Массив конфигурации.
     * @return array Объединённый массив конфигурации.
     */
    public function configure(array $config): array {
        $this->config = array_replace_recursive(self::configDefault(), $this->config, $config);
        return $this->config;
    }

    /**
     * Получает контейнер зависимостей.
     *
     * @return Container Объект контейнера зависимостей.
     */
    public function container(): Container {
        return $this->container;
    }

    /**
     * Запуск приложения.
     *
     * @return void
     */
    public function run() {
        HandlerException::register();

        $router = $this->container->router;
        $request = $this->container->request;
        $response = $this->container->response;

        $uri = $request->getUri();
        
        $response->addHeaders($this->config['headers'] ?? []);
        $response->setCompression($this->config['compression'] ?? 0);

        $matched = $router->push($this->config['routes'])->match($uri);
        $controller = $router->resolveController($matched);

        try {
            $data = $router->dispatch($controller);
            $response->setOutput($data);
            $response->init();
        } catch(\Throwable $exception) {
            HandlerException::handleException($exception);
        }
    }

    /**
     * Возвращает массив компонентов по умолчанию.
     *
     * Этот метод предоставляет стандартный набор компонентов, 
     * которые могут быть использованы приложением.
     *
     * @return array Возвращает массив компонентов.
     */
    public static function componentsDefault(): array {
        return [
            'loader' => \Sumish\Loader::class,
            'router' => \Sumish\Router::class,
            'request' => \Sumish\Request::class,
            'response' => \Sumish\Response::class,
            'session' => \Sumish\Session::class,
            'view' => \Sumish\View::class,
        ];
    }

    /**
     * Возвращает массив конфигурации по умолчанию.
     *
     * Этот метод предоставляет стандартный набор конфигурационных 
     * параметров, которые могут быть использованы приложением 
     * при его инициализации.
     *
     * @return array Возвращает массив конфигурации.
     */
    public static function configDefault(): array {
        return [
            'routes' => ['/' => ['controller' => 'MainController', 'action' => 'index']],
            'headers' => [1000 => 'Content-Type: text/html; charset=utf-8'],
            'components' => self::componentsDefault(),
            'libraries' => [],
            'db' => [
                'driver' => 'mysql',
                'host' => '',
                'database' => '',
                'user' => '',
                'password' => '',
                'charset' => 'utf8',
            ],
            'compression' => 0,
        ];
    }
}
