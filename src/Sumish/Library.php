<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Library для загрузки библиотек.
 *
 * Этот класс предоставляет метод для загрузки файлов 
 * библиотек в приложении. Он проверяет, существует ли 
 * указанный файл, и включает его, если он найден. 
 * Это упрощает управление зависимостями и подключение 
 * различных библиотек в рамках приложения.
 *
 * @package Sumish
 */
class Library {
    /**
     * Загружает библиотеку из указанного файла.
     *
     * Этот метод принимает путь к файлу библиотеки, проверяет, 
     * существует ли файл, и если да, то включает его с помощью 
     * функции require_once.
     *
     * @param string $file Путь к файлу библиотеки, который нужно загрузить.
     */
    public function load($file) {
        if (is_file($file)) {
            require_once $file;
        }
    }
}