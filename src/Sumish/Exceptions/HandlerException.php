<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish\Exceptions;

use Throwable;
use ErrorException;
use Sumish\Exceptions\NotFoundException;

/**
 * Класс HandlerException для обработки ошибок и исключений.
 * 
 * @package Sumish
 */
class HandlerException {
    /**
     * Инициализирует обработчики ошибок и исключений.
     */
    public static function register(): void {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    /**
     * Обрабатывает необработанные исключения.
     *
     * @param Throwable $exception Исключение для обработки.
     */
    public static function handleException(Throwable $exception): void {
        self::logException($exception);
        self::renderException($exception);
    }

    /**
     * Обрабатывает ошибки PHP.
     *
     * @param int $severity Уровень ошибки.
     * @param string $message Сообщение ошибки.
     * @param string $file Файл, где возникла ошибка.
     * @param int $line Строка, где возникла ошибка.
     * @throws ErrorException Исключение, преобразованное из ошибки.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): void {
        // Преобразуем ошибку в исключение
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Логирует исключение.
     *
     * @param Throwable $exception Исключение для логирования.
     */
    protected static function logException(Throwable $exception): void {
        error_log("{$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}");
    }

    /**
     * Обрабатывает и отображает исключение.
     *
     * Определяет тип исключения и рендерит соответствующую ошибку. 
     * Если шаблон недоступен, используется резервный HTML.
     *
     * @param Throwable $exception Исключение для обработки.
     */
    protected static function renderException(Throwable $exception): void {
        if ($exception instanceof NotFoundException) {
            http_response_code(404);
            self::renderTemplate('errors/404', [
                'message' => 'Page not found',
                'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            ], '404 Not Found', 'Sorry, the page you are looking for could not be found.');
            return;
        }

        http_response_code(500);
        self::renderTemplate('errors/500', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ], 'An error occurred', "{$exception->getMessage()}");
    }

    /**
     * Отображает шаблон ошибки или резервный HTML.
     *
     * Выполняет попытку рендера указанного шаблона. Если это невозможно, отображается
     * резервное сообщение с заголовком и описанием ошибки.
     *
     * @param string $template Шаблон ошибки.
     * @param array $data Данные для передачи в шаблон.
     * @param string $title Заголовок ошибки для резервного HTML.
     * @param string $message Сообщение ошибки для резервного HTML.
     */
    protected static function renderTemplate(string $template, array $data, string $title, string $message): void {
        try {
            $view = new \Sumish\View();
            echo $view->render($template, $data);
        } catch (Throwable $exception) {
            echo "<h1>{$title}</h1>";
            echo "<p>{$message}</p>";
        }
    }
}
