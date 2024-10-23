<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Главный класс для инициализации и управления приложением.
 *
 * Этот класс инициализирует контейнер зависимостей, загружает маршруты, библиотеки, 
 * а также управляет ошибками и отвечает за запуск контроллеров.
 *
 * @package Sumish
 * @version 1.0.0
 */
class Application {
    /**
     * Контейнер зависимостей.
     *
     * @var \Sumish\Container
     */
    private Container $container;

    /**
     * Конфигурация приложения.
     *
     * @var array
     */
    private array $config;

    /**
     * Конструктор класса Application.
     *
     * @param array $config Массив конфигурации приложения.
     */
    public function __construct(array $config = []) {
        $this->config = $this->configure($config);

        $this->initializeContainer();
        $this->initializeRoutes();
        $this->initializeLibraries();

        $this->setupResponse();
    }

    /**
     * Конфигурирует приложение, объединяя значения по умолчанию и пользовательские настройки.
     *
     * @param array $config Массив конфигурации.
     * @return array Объединённый массив конфигурации.
     */
    private function configure(array $config) {
        return array_replace_recursive(self::configDefault(), $config);
    }

    /**
     * Инициализирует контейнер зависимостей.
     *
     * Создаёт контейнер и регистрирует конфигурацию.
     */
    private function initializeContainer() {
        $components = $this->config['components'] ?? [];
        $this->container = Container::create(array_merge(self::componentsDefault(), $components));
        $this->container->register('config', $this->config);
    }

    /**
     * Инициализирует маршруты.
     *
     * Загружает маршруты из конфигурации.
     */
    private function initializeRoutes() {
        $routes = $this->config['routes'] ?? [];
        $this->container->route->load($routes);
    }

    /**
     * Инициализирует библиотеки.
     *
     * Загружает библиотеки из конфигурации.
     */
    private function initializeLibraries() {
        $libraries = $this->config['libraries'] ?? [];
        $this->container->load->libraries($libraries);
    }

    /**
     * Настраивает ответ.
     *
     * Устанавливает заголовки и параметры сжатия.
     */
    private function setupResponse() {
        $this->container->response->addHeaders($this->config['headers'] ?? []);
        $this->container->response->setCompression($this->config['compression'] ?? 0);
    }

    /**
     * Запускает приложение.
     *
     * Проверяет наличие контроллера и вызывает его метод dispatch.
     */
    public function run() {
        if ($this->container->controller) {
            $this->container->controller->dispatch();
        } else {
            $this->error(404);
        }

        $this->container->response->init();
    }

    /**
     * Обрабатывает ошибки и устанавливает сообщение об ошибке в ответ.
     *
     * @param int $status Код статуса ошибки.
     */
    private function error($status) {
        $errorText = 'Error ';
    
        switch ($status) {
            case 404:
                $errorText .= '404 Not Found';
                break;
            case 500:
                $errorText .= '500 Internal Server Error';
                break;
            case 403:
                $errorText .= '403 Forbidden';
                break;
            default:
                $errorText .= 'Unknown Error';
                break;
        }
    
        $this->container->response->setOutput($errorText);
    }

    /**
     * Возвращает массив компонентов по умолчанию.
     *
     * @return array Массив компонентов.
     */
    public static function componentsDefault() {
        return [
            'load' => \Sumish\Loader::class,
            'route' => \Sumish\Router::class,
            'session' => \Sumish\Session::class,
            'request' => \Sumish\Request::class,
            'response' => \Sumish\Response::class,
            'view' => \Sumish\View::class,
        ];
    }

    /**
     * Возвращает массив конфигурации по умолчанию.
     *
     * @return array Массив конфигурации.
     */
    public static function configDefault() {
        return [
            'routes' => ['/' => 'home'],
            'libraries' => [],
            'headers' => [1000 => 'Content-Type: text/html; charset=utf-8'],
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
        ];
    }
}
