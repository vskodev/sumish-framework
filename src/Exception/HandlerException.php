<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

namespace Sumish\Exception;

use Throwable;
use ErrorException;
use Sumish\View;
use Sumish\Exception\NotFoundException;

/**
 * Класс HandlerException для обработки ошибок и исключений.
 * 
 * @package Sumish\Exception
 */
class HandlerException {
    /**
     * Инициализирует обработчики ошибок и исключений.
     */
    public static function register(): void {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Обрабатывает ошибки PHP.
     *
     * @param int $errno Уровень ошибки.
     * @param string $errstr Сообщение об ошибке.
     * @param string $errfile Файл, где возникла ошибка.
     * @param int $errline Строка, где возникла ошибка.
     * @throws ErrorException Исключение, преобразованное из ошибки.
     * @return bool Всегда возвращает true, чтобы указать, что ошибка была обработана.
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Преобразуем ошибку в исключение
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
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
     * @param array<string, mixed> $data Данные для шаблона.
     * @param string $title Заголовок ошибки для резервного HTML.
     * @param string $message Сообщение ошибки для резервного HTML.
     */
    protected static function renderTemplate(string $template, array $data, string $title, string $message): void {
        try {
            $view = new View();
            echo $view->render($template, $data);
        } catch (Throwable $exception) {
            echo "<h1>{$title}</h1>";
            echo "<p>{$message}</p>";
        }
    }
}
