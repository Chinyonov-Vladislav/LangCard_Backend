<?php

namespace App\Exceptions;

use Exception;

class HunspellNotInstallException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('api.hunspell_not_installed'));
    }
}
