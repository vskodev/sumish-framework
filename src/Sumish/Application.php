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
<<<<<<< HEAD
<<<<<<< HEAD
    private function configure(array $config): array {
=======
    private function configure(array $config) {
>>>>>>> 721b66b (Multiple improvements)
=======
    private function configure(array $config): array {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return array_replace_recursive(self::configDefault(), $config);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Инициализирует контейнер зависимостей приложения.
     *
     * Этот метод создает контейнер и регистрирует в нем 
     * конфигурацию приложения.
     *
     * @return void
=======
     * Инициализирует контейнер зависимостей.
     *
     * Создаёт контейнер и регистрирует конфигурацию.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Инициализирует контейнер зависимостей приложения.
     *
     * Этот метод создает контейнер и регистрирует в нем 
     * конфигурацию приложения.
     *
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    private function initializeContainer() {
        $components = $this->config['components'] ?? [];
        $this->container = Container::create(array_merge(self::componentsDefault(), $components));
        $this->container->register('config', $this->config);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Инициализирует маршруты приложения.
     *
     * Этот метод загружает маршруты из конфигурации 
     * и устанавливает их в контейнере маршрутов.
     *
     * @return void
=======
     * Инициализирует маршруты.
     *
     * Загружает маршруты из конфигурации.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Инициализирует маршруты приложения.
     *
     * Этот метод загружает маршруты из конфигурации 
     * и устанавливает их в контейнере маршрутов.
     *
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    private function initializeRoutes() {
        $routes = $this->config['routes'] ?? [];
        $this->container->route->load($routes);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Инициализирует библиотеки приложения.
     *
     * Этот метод загружает библиотеки из конфигурации 
     * и устанавливает их в контейнере.
     *
     * @return void
=======
     * Инициализирует библиотеки.
     *
     * Загружает библиотеки из конфигурации.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Инициализирует библиотеки приложения.
     *
     * Этот метод загружает библиотеки из конфигурации 
     * и устанавливает их в контейнере.
     *
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    private function initializeLibraries() {
        $libraries = $this->config['libraries'] ?? [];
        $this->container->load->libraries($libraries);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Настраивает объект ответа приложения.
     *
     * Этот метод добавляет заголовки и устанавливает уровень сжатия 
     * для ответа на основе конфигурации приложения.
     *
     * @return void
=======
     * Настраивает ответ.
     *
     * Устанавливает заголовки и параметры сжатия.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Настраивает объект ответа приложения.
     *
     * Этот метод добавляет заголовки и устанавливает уровень сжатия 
     * для ответа на основе конфигурации приложения.
     *
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    private function setupResponse() {
        $this->container->response->addHeaders($this->config['headers'] ?? []);
        $this->container->response->setCompression($this->config['compression'] ?? 0);
    }

    /**
     * Запускает приложение.
     *
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     * Этот метод проверяет, установлен ли контроллер, и выполняет 
     * его метод dispatch для обработки запроса. Если контроллер 
     * не установлен, вызывается метод ошибки с кодом 404. 
     * Затем инициализируется объект ответа.
     *
     * @return void
<<<<<<< HEAD
=======
     * Проверяет наличие контроллера и вызывает его метод dispatch.
>>>>>>> 721b66b (Multiple improvements)
=======
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
<<<<<<< HEAD
     * Обрабатывает ошибки приложения.
     *
     * Этот метод устанавливает сообщение об ошибке в зависимости 
     * от переданного кода состояния и выводит его через объект ответа.
     *
     * @param int $status Код состояния ошибки.
     * @return void
=======
     * Обрабатывает ошибки и устанавливает сообщение об ошибке в ответ.
     *
     * @param int $status Код статуса ошибки.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Обрабатывает ошибки приложения.
     *
     * Этот метод устанавливает сообщение об ошибке в зависимости 
     * от переданного кода состояния и выводит его через объект ответа.
     *
     * @param int $status Код состояния ошибки.
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
<<<<<<< HEAD
     * Возвращает массив компонентов по умолчанию для контейнера.
     *
     * Этот метод предоставляет стандартный набор компонентов, 
     * которые могут быть использованы приложением.
     *
     * @return array Возвращает ассоциативный массив компонентов.
     */
    public static function componentsDefault(): array {
=======
     * Возвращает массив компонентов по умолчанию.
=======
     * Возвращает массив компонентов по умолчанию для контейнера.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод предоставляет стандартный набор компонентов, 
     * которые могут быть использованы приложением.
     *
     * @return array Возвращает ассоциативный массив компонентов.
     */
<<<<<<< HEAD
    public static function componentsDefault() {
>>>>>>> 721b66b (Multiple improvements)
=======
    public static function componentsDefault(): array {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
<<<<<<< HEAD
     * Возвращает массив конфигурации по умолчанию для приложения.
     *
     * Этот метод предоставляет стандартный набор конфигурационных 
     * параметров, которые могут быть использованы приложением 
     * при его инициализации.
     *
     * @return array Возвращает ассоциативный массив конфигурации.
     */
    public static function configDefault(): array {
=======
     * Возвращает массив конфигурации по умолчанию.
=======
     * Возвращает массив конфигурации по умолчанию для приложения.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод предоставляет стандартный набор конфигурационных 
     * параметров, которые могут быть использованы приложением 
     * при его инициализации.
     *
     * @return array Возвращает ассоциативный массив конфигурации.
     */
<<<<<<< HEAD
    public static function configDefault() {
>>>>>>> 721b66b (Multiple improvements)
=======
    public static function configDefault(): array {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
