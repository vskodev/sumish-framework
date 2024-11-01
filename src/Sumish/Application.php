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
 * @version 1.0.1
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
    private function configure(array $config): array {
        return array_replace_recursive(self::configDefault(), $config);
    }

    /**
     * Инициализирует контейнер зависимостей приложения.
     *
     * Этот метод создает контейнер и регистрирует в нем 
     * конфигурацию приложения.
     *
     * @return void
     */
    private function initializeContainer() {
        $components = $this->config['components'] ?? [];
        $this->container = Container::create(array_merge(self::componentsDefault(), $components));
        $this->container->register('config', $this->config);
    }

    public function container() {
        return $this->container;
    }

    /**
     * Инициализирует маршруты приложения.
     *
     * Этот метод загружает маршруты из конфигурации 
     * и устанавливает их в контейнере маршрутов.
     *
     * @return void
     */
    private function initializeRoutes() {
        $routes = $this->config['routes'] ?? [];
        $this->container->route->load($routes);
    }

    /**
     * Инициализирует библиотеки приложения.
     *
     * Этот метод загружает библиотеки из конфигурации 
     * и устанавливает их в контейнере.
     *
     * @return void
     */
    private function initializeLibraries() {
        $libraries = $this->config['libraries'] ?? [];
        $this->container->load->libraries($libraries);
    }

    /**
     * Настраивает объект ответа приложения.
     *
     * Этот метод добавляет заголовки и устанавливает уровень сжатия 
     * для ответа на основе конфигурации приложения.
     *
     * @return void
     */
    private function setupResponse() {
        $this->container->response->addHeaders($this->config['headers'] ?? []);
        $this->container->response->setCompression($this->config['compression'] ?? 0);
    }

    /**
     * Запускает приложение.
     *
     * Этот метод проверяет, установлен ли контроллер, и выполняет 
     * его метод dispatch для обработки запроса. Если контроллер 
     * не установлен, вызывается метод ошибки с кодом 404. 
     * Затем инициализируется объект ответа.
     *
     * @return void
     */
    public function run() {
        $controller = $this->container->controller;

        if ($controller) {
            $controller->dispatch();
        } else {
            $this->error(404);
        }

        $this->container->response->init();
    }

    /**
     * Обрабатывает ошибки приложения.
     *
     * Этот метод устанавливает сообщение об ошибке в зависимости 
     * от переданного кода состояния и выводит его через объект ответа.
     *
     * @param int $status Код состояния ошибки.
     * @return void
     */
    private function error($status) {
        $errorText = 'Error ';
    
        switch ($status) {
            case 403:
                $errorText .= '403 Forbidden';
                break;
            case 404:
                $errorText .= '404 Not Found';
                break;
            case 500:
                $errorText .= '500 Internal Server Error';
                break;
            default:
                $errorText .= 'Unknown Error';
                break;
        }
    
        $this->container->response->setOutput($errorText);
    }

    /**
     * Возвращает массив компонентов по умолчанию для контейнера.
     *
     * Этот метод предоставляет стандартный набор компонентов, 
     * которые могут быть использованы приложением.
     *
     * @return array Возвращает ассоциативный массив компонентов.
     */
    public static function componentsDefault(): array {
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
     * Возвращает массив конфигурации по умолчанию для приложения.
     *
     * Этот метод предоставляет стандартный набор конфигурационных 
     * параметров, которые могут быть использованы приложением 
     * при его инициализации.
     *
     * @return array Возвращает ассоциативный массив конфигурации.
     */
    public static function configDefault(): array {
        return [
            'routes' => ['/' => 'home'],
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
