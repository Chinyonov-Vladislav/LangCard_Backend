<?php

namespace App\DTO\DataFromIpGeolocation;

class LanguageFromIpGeolocationDTO
{
    private ?array $locales;

    public function __construct(?array $locales)
    {
        $this->locales = $locales;
    }
    public function getLocales(): ?array
    {
        return $this->locales;
    }
}
