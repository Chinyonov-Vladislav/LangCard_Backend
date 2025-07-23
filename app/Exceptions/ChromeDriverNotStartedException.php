<?php

namespace App\Exceptions;

use Exception;

class ChromeDriverNotStartedException extends Exception
{
    public function __construct()
    {
        parent::__construct("Не удалось запустить процесс chromedriver.exe");
    }
}
