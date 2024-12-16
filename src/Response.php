<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

/**
 * Класс Response для управления ответом HTTP.
 *
 * Этот класс отвечает за формирование и отправку HTTP-ответов,
 * включая заголовки, вывод и сжатие данных. Он предоставляет
 * методы для добавления заголовков, управления выходными данными,
 * установки HTTP-статуса и перенаправления.
 *
 * @package Sumish
 */
class Response {
    /**
     * Выходные данные, которые будут отправлены в ответе.
     *
     * @var string
     */
    private string $output = '';

    /**
     * HTTP-заголовки ответа.
     *
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * Уровень сжатия для данных, отправляемых в ответе.
     *
     * @var int
     */
    private int $level = 0;

    /**
     * Устанавливает HTTP-статус для ответа.
     *
     * @param int $status Код HTTP-статуса (по умолчанию 200).
     */
    public function setStatusCode(int $status = 200): void {
        http_response_code($status);
    }

    /**
     * Добавляет заголовок в список заголовков.
     *
     * @param string $header Заголовок, который нужно добавить.
     * @return void
     */
    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    /**
     * Добавляет массив заголовков в список.
     *
     * @param array<string> $headers Список заголовков.
     * @return void
     */
    public function addHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * Получает все заголовки.
     *
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Устанавливает выходные данные для ответа.
     *
     * @param string|null $output Данные, которые нужно установить в качестве ответа.
     * @throws \InvalidArgumentException Если передано значение null.
     */
    public function setOutput(string|null $output): void {
        if ($output === null) {
            throw new \InvalidArgumentException('Output cannot be null.');
        }

        $this->output = $output;
    }

    /**
     * Добавляет текст к выходным данным.
     *
     * @param string|null $text Текст, который нужно добавить.
     */
    public function addOutput(?string $text): void {
        $this->output .= $text ?? '';
    }

    /**
     * Возвращает текущие выходные данные.
     *
     * @return string Текущие выходные данные.
     */
    public function getOutput(): string {
        return $this->output;
    }

    /**
     * Сжимает данные для отправки в ответе.
     *
     * Этот метод проверяет, нужно ли сжать данные, и выполняет сжатие,
     * если это необходимо.
     *
     * @param string|null $data Данные для сжатия (по умолчанию текущие выходные данные).
     * @return string Сжатые данные или оригинальные данные, если сжатие не применимо.
     */
    public function compressOutput(?string $data = null, bool $skipZlib = false): string {
        if (is_null($data)) {
            $data = $this->output;
        }

        // Если заголовки уже отправлены, сжатие не имеет смысла
        if (headers_sent() || connection_status()) {
            return $data;
        }

        // Если нет расширения zlib или включено сжатие на сервере, не сжимаем
        if ($skipZlib || !extension_loaded('zlib') || ini_get('zlib.output_compression')) {
            return $data;
        }

        $compressionType = $this->detectCompressionType();
        if ($compressionType === null || $this->level < -1 || $this->level > 9) {
            return $data;
        }
    
        $this->addHeader('Content-Encoding: ' . $compressionType);
        return gzencode($data, $this->level);
    }

    /**
     * Устанавливает уровень сжатия для ответа.
     *
     * @param int $level Уровень сжатия (от -1 до 9).
     */
    public function setCompression(int $level): void {
        $this->level = $level;
    }

    /**
     * Определяет, какой тип сжатия использовать.
     *
     * Этот метод проверяет, поддерживает ли клиент сжатие, и возвращает тип сжатия.
     * Возвращает 'gzip', 'x-gzip' или null, если сжатие не требуется.
     *
     * @return string|null Тип сжатия или null, если сжатие не требуется.
     */
    private function detectCompressionType(): ?string {
        // Проверяем наличие уровня сжатия и заголовков
        if ($this->level && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
                return 'x-gzip';
            }

            if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                return 'gzip';
            }
        }
    
        return null;
    }

    /**
     * Инициализирует ответ, добавляя заголовки и обрабатывая сжатие.
     *
     * Этот метод выполняет все действия, необходимые для отправки окончательного
     * ответа, включая сжатие данных, добавление заголовков и вывод данных.
     *
     * @return void Отправляет выходные данные клиенту.
     */
    public function send(): void {
        if ($this->level) {
            $this->output = $this->compressOutput();
        }

        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header, true);
            }
        }

        print($this->output);
    }

    /**
     * Отправляет HTTP-редирект.
     *
     * Этот метод выполняет редирект на указанный URL с заданным HTTP-статусом.
     *
     * @param string $url URL для перенаправления.
     * @param int $status Код HTTP-статуса для редиректа (по умолчанию 302).
     */
    public function redirect(string $url, int $status = 302): void {
        $this->addHeader("Location: $url");
        $this->setStatusCode($status);
        $this->send();
    }
}
