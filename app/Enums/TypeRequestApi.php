<?php

namespace App\Enums;

enum TypeRequestApi: string
{
    case currencyRequest = "currencyRequest";
    case timezoneRequest = "timezoneRequest";

    case languageRequest = "languageRequest";

    case coordinatesRequest = "coordinatesRequest";
    case allRequests = "allRequests";
}
