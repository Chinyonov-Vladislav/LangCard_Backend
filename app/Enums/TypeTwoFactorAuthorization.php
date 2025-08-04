<?php

namespace App\Enums;

enum TypeTwoFactorAuthorization:string
{
    case email = 'email';
    case googleAuthenticator = 'googleAuthenticator';
}
