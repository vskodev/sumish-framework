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
     * Рендерит страницу ошибки.
     *
     * @param Throwable $exception Исключение для отображения.
     */
    protected static function renderException(Throwable $exception): void {
        $view = new \Sumish\View();

        if ($exception instanceof NotFoundException) {
            http_response_code(404);
            try {
                echo $view->render('errors/404', [
                    'message' => 'Page not found',
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/',
                ]);
            } catch (Throwable $exception) {
                echo "<h1>404 Not Found</h1>";
                echo "<p>Sorry, the page you are looking for could not be found.</p>";
                echo "<p>URI: " . ($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
            }
            return;
        }

        http_response_code(500);
        try {
            echo $view->render('errors/500', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ]);
        } catch (Throwable $exception) {
            echo "<h1>An error occurred</h1>";
            echo "<p>Message: {$exception->getMessage()}</p>";
            echo "<p>Code: {$exception->getCode()}</p>";
        }
    }
}
