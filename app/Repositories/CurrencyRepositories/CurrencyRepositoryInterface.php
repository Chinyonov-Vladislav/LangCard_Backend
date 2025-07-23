<?php

namespace App\Repositories\CurrencyRepositories;

use App\Models\Currency;

interface CurrencyRepositoryInterface
{

    public function getAllIdCurrencies();

    public function isExistCurrencyById(int $currencyId);
    public function isExistByCode($code);
    public function getByCode($code);
    public function saveNewCurrency(string $name, string $code, string $symbol);
}
