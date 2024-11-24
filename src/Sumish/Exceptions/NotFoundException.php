<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish\Exceptions;

/**
 * Исключение для обработки ситуации, когда ресурс не найден.
 *
 * Устанавливает код ошибки HTTP 404.
 *
 * @package Sumish
 */
class NotFoundException extends \Exception {
    /**
     * Код ошибки HTTP.
     *
     * @var int
     */
    protected $code = 404;
}
