<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Response для управления ответом HTTP.
 *
 * Этот класс отвечает за формирование и отправку HTTP-ответов, 
 * включая заголовки, вывод и сжатие данных. Он предоставляет 
 * методы для добавления заголовков, установки и управления 
 * выходными данными, а также перенаправления.
 *
 * @package Sumish
 */
class Response {
    /**
     * Выходные данные, которые будут отправлены в ответе.
     *
     * @var string
     */
    private $output;

    /**
     * Заголовки для отправки в ответе.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Уровень сжатия для выходных данных.
     *
     * @var int
     */
    private $level = 0;

    /**
     * Добавляет заголовок к ответу.
     *
     * @param string $header Заголовок, который нужно добавить.
     */
    public function addHeader($header) {
        $this->headers[] = $header;
    }

    /**
     * Добавляет несколько заголовков к ответу.
     *
     * @param array $headers Массив заголовков, которые нужно добавить.
     */
    public function addHeaders($headers = []) {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * Получает выходные данные из объекта Response.
     *
     * Этот метод возвращает текущие выходные данные, 
     * которые будут отправлены в HTTP-ответе. 
     * Он предназначен для доступа к приватному свойству 
     * output из других классов.
     *
     * @return string Возвращает выходные данные.
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * Добавляет выходные данные к текущему выводу.
     *
     * @param string $output Выходные данные, которые нужно добавить.
     */
    public function addOutput($output) {
        $this->output .= $output;
    }

    /**
     * Добавляет текст к выходным данным.
     *
     * @param string $text Текст, который нужно добавить к выходу.
     */
    public function print($text) {
        $this->addOutput($text);
    }

    /**
     * Устанавливает выходные данные для ответа.
     *
     * @param string $output Выходные данные, которые нужно установить.
     */
    public function setOutput($output) {
        $this->output = $output;
    }

    /**
     * Устанавливает уровень сжатия для ответа.
     *
     * @param int $level Уровень сжатия (от -1 до 9).
     */
    public function setCompression($level) {
        $this->level = $level;
    }

    /**
     * Перенаправляет на указанный URL.
     *
     * @param string $url URL, на который нужно перенаправить.
     * @param int $status Статус перенаправления (по умолчанию 302).
     */
    public function redirect($url, $status = 302) {
        header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status);
        exit;
    }

    /**
     * Сжимает данные для отправки в ответе.
     *
     * @param string|null $data Данные для сжатия (по умолчанию текущие выходные данные).
     * @return string|null Возвращает сжатые данные или оригинальные данные, 
     *                     если сжатие не применимо.
     */
    private function compress($data = null) {
        if (is_null($data)) {
            $data = $this->output;
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
            $encoding = 'gzip';
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
            $encoding = 'x-gzip';
        }

        if (!isset($encoding) || ($this->level < -1 || $this->level > 9)) {
            return $data;
        }

        if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
            return $data;
        }

        if (headers_sent()) {
            return $data;
        }

        if (connection_status()) {
            return $data;
        }

        $this->addHeader('Content-Encoding: ' . $encoding);

        return gzencode($data, (int)$this->level);
    }

    /**
     * Инициализирует ответ, добавляя заголовки и обрабатывая сжатие.
     *
     */
    public function init() {
        if ($this->level) {
            $this->output = $this->compress();
        }

        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header, true);
            }
        }

        print($this->output);
    }
}
