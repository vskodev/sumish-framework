<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Класс View для работы с шаблонами.
 *
 * Поддерживает Twig в качестве основного обработчика шаблонов.
 * Возможно использовать для обработки шаблонов PHP.
 * 
 * @package Sumish
 */
class View {
    /**
     * Экземпляр Twig или null, если Twig недоступен.
     *
     * @var Environment|null
     */
    private ?Environment $twig = null;

    /**
     * Путь к директории шаблонов.
     *
     * @var string
     */
    private string $path;

    /**
     * Инициализирует View и настраивает Twig, если он доступен.
     */
    public function __construct() {
        $this->path = getcwd() . '/app/templates';

        if (class_exists(Environment::class)) {
            $loader = new FilesystemLoader($this->path);
            $this->twig = new Environment($loader);
        }
    }

    /**
     * Рендерит шаблон.
     *
     * @param string $template Имя шаблона (например, '<name>/file').
     * @param array $data Данные для шаблона.
     * @return string Содержимое шаблона.
     * @throws Exception Если файл не найден или произошла ошибка рендера.
     */
    public function render(string $template, array $data = []): string {
        // Если Twig доступен и файл с расширением .twig существует, используем Twig
        if ($this->twig && is_file("{$this->path}/{$template}.twig")) {
            return $this->twig->render("{$template}.twig", $data);
        }

        // В противном случае рендерим через PHP
        $templatePath = "{$this->path}/{$template}.php";

        if (!is_file($templatePath)) {
            throw new \Exception("Template file not found: {$templatePath}");
        }

        extract($data, EXTR_OVERWRITE);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
