<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Request для обработки входящих HTTP-запросов.
 *
 * Этот класс отвечает за получение и очистку данных, 
 * поступающих в приложение через глобальные массивы PHP 
 * ($_GET, $_POST, $_REQUEST, $_COOKIE, $_FILES, $_SERVER).
 * Он помогает избежать проблем с безопасностью, 
 * экранируя входящие данные.
 *
 * @package Sumish
 */
class Request {
    /**
     * Данные, полученные через метод GET.
     *
     * @var array
     */
    public $get = [];

    /**
     * Данные, полученные через метод POST.
     *
     * @var array
     */
    public $post = [];

    /**
     * Данные, полученные через метод REQUEST.
     *
     * @var array
     */
    public $request = [];

    /**
     * Данные, полученные через куки.
     *
     * @var array
     */
    public $cookie = [];

    /**
     * Данные, полученные через загруженные файлы.
     *
     * @var array
     */
    public $files = [];

    /**
     * Данные, полученные через серверные переменные.
     *
     * @var array
     */
    public $server = [];
    
    /**
     * Конструктор класса Request.
     *
     * Этот метод инициализирует все свойства класса, 
     * очищая данные, полученные через соответствующие 
     * глобальные массивы.
     */
    function __construct() {
        $this->get = $this->clean($_GET);
        $this->post = $this->clean($_POST);
        $this->request = $this->clean($_REQUEST);
        $this->cookie = $this->clean($_COOKIE);
        $this->files = $this->clean($_FILES);
        $this->server = $this->clean($_SERVER);
    }

    /**
     * Очищает данные, экранируя специальные символы.
     *
     * Этот метод рекурсивно очищает входящие данные, 
     * экранируя специальные HTML-символы для защиты 
     * от XSS-атак.
     *
     * @param mixed $data Данные, которые нужно очистить.
     * @return mixed Очищенные данные.
     */
    public function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);

                $data[$this->clean($key)] = $this->clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }

        return $data;
    }
}
