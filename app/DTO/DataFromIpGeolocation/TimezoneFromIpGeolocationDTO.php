<?php

namespace App\DTO\DataFromIpGeolocation;

class TimezoneFromIpGeolocationDTO
{
    private ?string $timezoneName;

    public function __construct(?string $timezoneName)
    {
        $this->timezoneName = $timezoneName;
    }
    public function getTimezoneName(): ?string
    {
        return $this->timezoneName;
    }
}
