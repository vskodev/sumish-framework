<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Loader для управления загрузкой моделей, контроллеров и библиотек.
 *
 * Этот класс предоставляет методы для динамической загрузки моделей, 
 * контроллеров и библиотек в приложении. Он облегчает процесс 
 * интеграции различных компонентов, обеспечивая удобный интерфейс 
 * для их подключения.
 *
 * @package Sumish
 */
class Loader {
    /**
     * Контейнер зависимостей.
     *
     * @var \Sumish\Container
     */
    private Container $container;

    /**
     * Конструктор класса Loader.
     *
     * @param Container $container Контейнер зависимостей, который будет использоваться загрузчиком.
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Загружает модель по указанному имени.
     *
     * Этот метод принимает имя модели, проверяет наличие соответствующего 
     * файла и включает его, если он найден. Также создаёт и возвращает 
     * экземпляр модели.
     *
     * @param string $model Имя модели, которую нужно загрузить.
     * @return mixed Возвращает экземпляр модели или false, если модель не найдена.
     */
    public function model($model) {
        if (strrpos($model, '/')) {
            $parts = explode('/', $model);
            $path = DIR_APP . '/' . $parts[0];
            $model = $parts[1];
        } else {
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
        }

        return false;
    }

    /**
     * Загружает контроллер по указанному имени.
     *
     * Этот метод загружает и возвращает экземпляр контроллера, 
     * вызывая соответствующее действие контроллера.
     *
     * @param string $controller Имя контроллера, который нужно загрузить.
     * @return mixed Возвращает результат выполнения контроллера или false, если контроллер не найден.
     */
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

    /**
     * Загружает библиотеку по указанному имени.
     *
     * Этот метод ищет и включает файл библиотеки, проверяя наличие 
     * соответствующих файлов, а также вызывает метод init, 
     * если он существует в классе библиотеки.
     *
     * @param string $library Имя библиотеки, которую нужно загрузить.
     * @return bool Возвращает true, если библиотека успешно загружена.
     */
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

    /**
     * Загружает библиотеку по указанному имени (псевдоним для library).
     *
     * @param string $name Имя библиотеки, которую нужно загрузить.
     * @return bool Возвращает true, если библиотека успешно загружена.
     */
    public function lib($name) {
        return $this->library($name);
    }

    /**
     * Загружает массив библиотек.
     *
     * Этот метод принимает массив имен библиотек и загружает каждую из них.
     *
     * @param array $libraries Массив имен библиотек, которые нужно загрузить.
     */
    public function libraries($libraries) {
        if (is_array($libraries)) {
            foreach ($libraries as $name) {
                $this->lib($name);
            }
        }
    }

    /**
     * Загружает маршруты.
     *
     * Этот метод загружает маршруты в контейнере.
     *
     * @param array $routes Массив маршрутов, которые нужно загрузить.
     */
    public function routes($routes) {
        $this->container->route->load($routes);
    }
}
