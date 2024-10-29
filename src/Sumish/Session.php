<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

/**
 * Класс Session для управления сессиями.
 *
 * Этот класс обрабатывает управление сессиями в приложении, 
 * включая инициализацию сессий, получение идентификатора сессии 
 * и уничтожение сессий. Он также устанавливает настройки 
 * безопасности для работы сессий, чтобы защитить данные.
 *
 * @package Sumish
 */
class Session {
    /**
     * Массив для хранения данных сессии.
     *
     * @var array
     */
    public $data = [];

    /**
     * Конструктор класса Session.
     *
     * Этот метод инициализирует сессию, устанавливая настройки безопасности 
     * и начиная сессию, если она еще не была начата.
     */
    public function __construct() {
        if (!session_id()) {
            ini_set('session.use_only_cookies', 'On'); // Использовать только куки для идентификации сессии
            ini_set('session.use_trans_sid', 'Off');   // Отключить передачу идентификатора сессии через URL
            ini_set('session.cookie_httponly', 'On');  // Запретить доступ к кукам из JavaScript

            session_set_cookie_params(0, '/'); // Установить параметры куки
            session_start(); // Начать сессию
        }

        $this->data =& $_SESSION; // Ссылка на глобальный массив сессий
    }

    /**
     * Получает идентификатор текущей сессии.
     *
     * @return string Возвращает идентификатор текущей сессии.
     */
    public function getId() {
        return session_id();
    }

    /**
     * Уничтожает текущую сессию.
     *
     * Этот метод завершает сессию и очищает все данные, 
     * связанные с ней.
     *
     * @return bool Возвращает true, если сессия успешно уничтожена, иначе false.
     */
    public function destroy() {
        return session_destroy(); // Уничтожить сессию
    }
}
