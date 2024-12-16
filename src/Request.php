<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

/**
 * Класс Request для обработки входящих HTTP-запросов.
 *
 * Этот класс отвечает за получение и очистку данных,
 * поступающих в приложение через глобальные массивы PHP
 * ($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER).
 * Он предоставляет удобный интерфейс для работы с запросами
 * и гарантирует безопасность данных.
 *
 * @package Sumish
 */
class Request {
    /**
     * Параметры GET-запроса.
     *
     * @var array<string, string|array<string>|null>
     */
    private array $get = [];

    /**
     * Параметры POST-запроса.
     *
     * @var array<string, string|array<string>|null>
     */
    private array $post = [];

    /**
     * Общие параметры запроса (объединение $_GET и $_POST).
     *
     * @var array<string, string|array<string>|null>
     */
    private array $request = [];

    /**
     * Параметры cookie-запроса.
     *
     * @var array<string, string|null>
     */
    private array $cookie = [];

    /**
     * Загруженные файлы.
     *
     * @var array<string, array{
     *     name: string|array<string>,
     *     type: string|array<string>,
     *     tmp_name: string|array<string>,
     *     error: int|array<int>,
     *     size: int|array<int>
     * }>
     */
    private array $files;

    /**
     * Информация о сервере и HTTP-запросе.
     *
     * @var array<string, string|int|float|bool|null>
     */
    private array $server = [];

    /**
     * Конструктор класса Request.
     *
     * Инициализирует все свойства класса, очищая данные,
     * полученные через соответствующие глобальные массивы.
     */
    public function __construct() {
        $this->get = $this->clean($_GET);
        $this->post = $this->clean($_POST);
        $this->request = $this->clean($_REQUEST);
        $this->cookie = $this->clean($_COOKIE);
        $this->files = $this->clean($_FILES);
        $this->server = $this->clean($_SERVER);
    }

    /**
     * Магический метод для доступа к разрешённым приватным свойствам.
     *
     * @param string $name Имя свойства.
     * @return array<string, string|array<string>|int|float|bool|null>|null Возвращает массив данных или null.
     */
    public function __get(string $name): ?array {
        $allowedProperties = ['get', 'post', 'request', 'cookie', 'files', 'server'];
        return in_array($name, $allowedProperties, true) ? $this->{$name} : null;
    }

    /**
     * Возвращает текущий URI запроса.
     *
     * @return string Полный URI запроса без строки запроса (Query String).
     */
    public function getUri(): string {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . trim($path ?? '', '/');
    }

    /**
     * Возвращает текущий метод HTTP-запроса.
     *
     * @return 'GET'|'POST'|'PUT'|'DELETE'|'PATCH'|'OPTIONS'|'HEAD' HTTP-метод.
     */
    public function getMethod(): string {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];
        return in_array($method, $validMethods, true) ? $method : 'GET';
    }

    /**
     * Проверяет, является ли HTTP-запрос методом GET.
     *
     * @return bool true, если запрос был отправлен с методом GET, иначе false.
     */
    public function get(): bool {
        return $this->getMethod() === 'GET';
    }

    /**
     * Проверяет, является ли HTTP-запрос методом POST.
     *
     * @return bool true, если запрос был отправлен с методом POST, иначе false.
     */
    public function post(): bool {
        return $this->getMethod() === 'POST';
    }

    /**
     * Очищает данные, экранируя специальные символы.
     *
     * @param mixed $data Данные, которые нужно очистить.
     * @return mixed Очищенные данные.
     */
    private function clean($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'clean'], $data);
        }

        return trim(htmlspecialchars((string)$data, ENT_COMPAT, 'UTF-8'));
    }
}
