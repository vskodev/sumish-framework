<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

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
class View
{
    /**
     * Путь к директории шаблонов.
     *
     * @var string
     */
    public string $path;

    /**
     * Экземпляр Twig или null, если Twig недоступен.
     *
     * @var Environment|null
     */
    private ?Environment $twig;

    /**
     * Конструктор класса View.
     * 
     * Инициализирует путь к шаблонам и Twig (если доступен).
     *
     * @param string|null $path Путь к шаблонам (по умолчанию — 'app/Views').
     */
    public function __construct(string $path = null)
    {
        $this->path = $path ?? dirname(getcwd()) . '/app/Views';
        $this->twig = $this->createTwigEnvironment();
    }

    /**
     * Создаёт и возвращает экземпляр Twig, если он доступен.
     *
     * @return Environment|null Экземпляр Twig или null, если использование недоступно.
     */
    private function createTwigEnvironment(): ?Environment
    {
        return class_exists(Environment::class) 
            ? new Environment(new FilesystemLoader($this->path))
            : null;
    }

    /**
     * Рендерит шаблон.
     *
     * @param string $template Имя шаблона (например, '<name>/file').
     * @param array<string, mixed> $data Данные для шаблона.
     * @return string Содержимое шаблона.
     * @throws \RuntimeException Если файл не найден или произошла ошибка рендера.
     */
    public function render(string $template, array $data = []): string
    {
        $twigTemplate = "{$this->path}/{$template}.twig";
        $phpTemplate = "{$this->path}/{$template}.php";
    
        if (is_file( $twigTemplate) && $this->twig) {
            return $this->twig->render("{$template}.twig", $data);
        }
    
        if (is_file($phpTemplate)) {
            return $this->renderPhpTemplate($phpTemplate, $data);
        }
    
        throw new \RuntimeException("Template file not found: {$template}");
    }

    /**
     * Рендерит PHP-шаблон.
     *
     * @param string $filePath Полный путь к PHP-файлу шаблона.
     * @param array<string, mixed> $data Данные для шаблона.
     * @return string Содержимое рендера.
     */
    private function renderPhpTemplate(string $filePath, array $data): string
    {
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require $filePath;
        return ob_get_clean();
    }
}
