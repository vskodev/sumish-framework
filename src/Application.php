<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

use Sumish\Exception\HandlerException;

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
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * Конструктор класса Application.
     *
     * @param array{
     *     components?: array<string, mixed>,
     *     db?: array<string, mixed>,
     * } $config Массив конфигурации приложения.
     */
    public function __construct(array $config = []) {
        $this->configure($config);
        $this->container = Container::create($this->config);
    }

    /**
     * Конфигурирует приложение, объединяя значения по умолчанию.
     *
     * @param array<string, mixed> $config Массив конфигурации.
     * @return array<string, mixed> Объединённый массив конфигурации.
     */
    public function configure(array $config): array {
        $this->config = array_replace_recursive(self::configDefault(), $this->config, $config);
        return $this->config;
    }

    /**
     * Возвращает конфигурацию приложения.
     *
     * @return array<string, mixed> Конфигурация приложения.
     */
    public function config(): array {
        return $this->config;
    }

    /**
     * Запуск приложения.
     *
     * @return void
     * @throws \Throwable Если возникает исключение при обработке запроса.
     */
    public function run() {
        HandlerException::register();

        $request = $this->container->get('request');
        $response = $this->container->get('response');
        $router = $this->container->get('router');

        $requestUri = $request->getUri();
        $matched = $router->push($this->config['routes'])->match($requestUri);
        $controller = $router->resolveController($matched);

        try {
            $data = $router->dispatch($controller);
            $response->addHeaders($this->config['headers']);
            $response->setCompression($this->config['compression']);
            $response->setOutput($data);
            $response->send();
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
     * @return array<string, class-string> Массив компонентов по умолчанию.
     */
    public static function componentsDefault(): array {
        return [
            'request' => \Sumish\Request::class,
            'response' => \Sumish\Response::class,
            'router' => \Sumish\Router::class,
            'loader' => \Sumish\Loader::class,
            'view' => \Sumish\View::class,
            'session' => \Sumish\Session::class,
        ];
    }

    /**
     * Возвращает массив конфигурации по умолчанию.
     *
     * Этот метод предоставляет стандартный набор конфигурационных 
     * параметров, которые могут быть использованы приложением 
     * при его инициализации.
     *
     * @return array{
     *     routes: array<string, array{controller: string, action: string}>,
     *     headers: array<int, string>,
     *     components: array<string, class-string>,
     *     libraries: array<string, mixed>,
     *     db: array{
     *         driver: string,
     *         host: string,
     *         database: string,
     *         user: string,
     *         password: string,
     *         charset: string
     *     },
     *     compression: int
     * }
     */
    public static function configDefault(): array {
        return [
            'routes' => ['/' => ['controller' => 'MainController', 'action' => 'index']],
            'headers' => [
                'Content-Type: text/html; charset=utf-8',
                'X-Generator: Sumish',
                'X-Powered-By: PHP'
            ],
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
