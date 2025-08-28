<?php

namespace App\DTO\DataFromIpGeolocation;

class CurrencyFromIpGeolocationDTO
{
    private string $name;
    private string $code;
    private string $symbol;

    public function __construct(string $name, string $code, string $symbol)
    {
        $this->name = $name;
        $this->code = $code;
        $this->symbol = $symbol;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getSymbol(): string
    {
        return $this->symbol;
    }
}
