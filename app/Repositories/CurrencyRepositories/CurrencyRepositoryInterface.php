<?php

namespace App\Repositories\CurrencyRepositories;

use App\DTO\DataFromIpGeolocation\CurrencyFromIpGeolocationDTO;

interface CurrencyRepositoryInterface
{

    public function getCurrencyIdByDataFromApi(?CurrencyFromIpGeolocationDTO $currencyDataDTO): ?int;

    public function getAllIdCurrencies();

    public function isExistCurrencyById(int $currencyId);
    public function isExistByCode($code);
    public function getByCode($code);
    public function saveNewCurrency(string $name, string $code, string $symbol);
}
