<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс View для обработки и отображения шаблонов.
 *
 * Этот класс отвечает за рендеринг шаблонов, используя 
 * разные движки шаблонов (например, Twig). Он управляет 
 * загрузкой шаблонов, передачей данных и выводом 
 * сгенерированного HTML-кода.
 *
 * @package Sumish
 */
class View {
    /**
     * Контейнер зависимостей.
     *
     * @var \Sumish\Container
     */
    private Container $container;

    /**
     * Движок шаблонов по умолчанию.
     *
     * @var string
     */
    private $processor = 'twig';

    /**
     * Расширение файла шаблона.
     *
     * @var string
     */
    public $ext = '.tpl';

    /**
     * Путь к шаблону.
     *
     * @var string
     */
    private $path;

    /**
     * Полное имя файла шаблона.
     *
     * @var string
     */
    private $file;

    /**
     * Имя шаблона.
     *
     * @var string
     */
    private $template;

    /**
     * Конструктор класса View.
     *
     * @param Container $container Контейнер зависимостей, который будет использоваться для доступа к другим компонентам.
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Рендерит данные для указанного шаблона.
     *
     * @param string $template Имя шаблона.
     * @param array $data Данные для передачи в шаблон.
     * @return string Сгенерированный HTML-код.
     */
    public function renderOutput(string $template, array $data = []): string {
        $this->template = $template . $this->ext;
        $this->detectPath();
        return $this->process($data);
    }

    /**
     * Рендерит шаблон и выводит его на экран.
     *
     * @param string $template Имя шаблона.
     * @param array $data Данные для передачи в шаблон.
     */
    public function render($template, array $data = []) {
        $output = $this->renderOutput($template, $data);
        $this->print($output);
    }

    /**
     * Обрабатывает данные и рендерит шаблон.
     *
     * @param array $data Данные для передачи в шаблон.
     * @return string Сгенерированный HTML-код.
     */
    public function process($data) {
        return $this->checkTwig()
            ? $this->processTwig($data)
            : $this->processDynamic($data);
    }

    /**
     * Выводит текст.
     *
     * @param string $text Текст для вывода.
     */
    public function print($text) {
        $this->container->response->print($text);
    }

    /**
     * Обрабатывает шаблон динамически.
     *
     * @param array $data Данные для передачи в шаблон.
     * @return string|false Сгенерированный HTML-код или false при ошибке.
     */
    protected function processDynamic(array $data = []) {
        $file = $this->file;

        if ($file && $data) {
            if (is_file($this->file)) {
                ob_start();
                extract($data, EXTR_SKIP);
                include_once($file);
                $output = ob_get_contents();
                ob_end_clean();
                return $output;
            }
        }

        return false;
    }

    /**
     * Обрабатывает шаблон с использованием Twig.
     *
     * @param array $data Данные для передачи в шаблон.
     * @return string|false Сгенерированный HTML-код или false при ошибке.
     */
    protected function processTwig(array $data = []) {
        static $twig = null;
        $checkTwig = false;
        $template = $this->template;
        $path = $this->path;

        if (is_null($twig)) {
            if (is_file($this->file)) {
                $loader = new \Twig_Loader_Filesystem($path);
                $twig = new \Twig_Environment($loader);
                $checkTwig = true;
            }
        }

        if ($checkTwig) {
            return $twig->render($template, $data);
        }

        return false;
    }

    /**
     * Проверяет, установлен ли Twig как движок шаблонов.
     *
     * @return bool Возвращает true, если Twig доступен, иначе false.
     */
    private function checkTwig() {
        return ($this->processor == 'twig' &&
                class_exists('Twig_Loader_Filesystem'));
    }

    /**
     * Определяет путь к шаблону.
     */
    private function detectPath() {
        $this->file = $this->getPathTemplate();
        if (!is_file($this->file)) {
            $this->file = $this->getPathTemplate(DIR_ROOT);
        }
    }

    /**
     * Получает путь к шаблону.
     *
     * @param string|null $path Путь к директории (по умолчанию - путь приложения).
     * @return string Полный путь к шаблону.
     */
    private function getPathTemplate($path = null) {
        $this->path = is_null($path)
              ? $this->container->app->path . '/templates'
              : $path . '/templates/' . $this->container->app->name;

        return $this->path . '/' . $this->template;
    }
}
