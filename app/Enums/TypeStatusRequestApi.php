<?php

namespace App\Enums;

enum TypeStatusRequestApi: string
{
    case success = 'success';
    case error = 'error';
    case delayed = 'delayed';
}
