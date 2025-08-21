<?php

namespace App\Enums;

enum TypeRequestApi: int
{
    case currencyRequest = 0;
    case timezoneRequest = 1;

    case languageRequest = 2;

    case coordinatesRequest = 3;
    case allRequests = 4;
}
