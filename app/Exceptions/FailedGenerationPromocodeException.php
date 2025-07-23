<?php

namespace App\Exceptions;

use Exception;

class FailedGenerationPromocodeException extends Exception
{
    public function __construct(int $countAttempts)
    {
        parent::__construct("Не удалось сгенерировать промокод за следующее количество попыток = $countAttempts");
    }
}
