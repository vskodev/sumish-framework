<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

/**
 * Класс Session для управления сессиями.
 *
 * Этот класс обрабатывает управление сессиями в приложении,
 * включая инициализацию сессий, получение идентификатора сессии и завершение сессии.
 *
 * @package Sumish
 */
class Session {
    /**
     * Массив для хранения данных сессии.
     *
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * Конструктор класса Session.
     *
     * Инициализирует сессию, устанавливая настройки безопасности.
     */
    public function __construct() {
        if (!session_id()) {
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_httponly', '1');
            session_set_cookie_params(0, '/');
            session_start();
        }
    
        $this->data =& $_SESSION;
    }

    /**
     * Получает идентификатор текущей сессии.
     *
     * @return string Идентификатор текущей сессии.
     */
    public function getId(): string {
        return session_id();
    }

    /**
     * Завершает текущую сессию и очищает все данные.
     *
     * @return bool Возвращает true, если сессия успешно уничтожена, иначе false.
     */
    public function destroy(): bool {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset(); // Очищает данные сессии
            session_destroy(); // Уничтожает сессию
            $this->data = [];
            return true;
        }
        return false;
    }
}
