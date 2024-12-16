<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

namespace Sumish\Exception;

/**
 * Исключение для обработки ситуации, когда ресурс не найден.
 *
 * Устанавливает код ошибки HTTP 404.
 *
 * @package Sumish\Exception
 */
class NotFoundException extends \Exception {
    /**
     * Код ошибки HTTP.
     *
     * @var int
     */
    protected $code = 404;
}
