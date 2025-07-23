<?php

namespace App\Exceptions;

use Exception;

class ProcessHunspellCheckException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('api.error_working_hunspell'));
    }
}
