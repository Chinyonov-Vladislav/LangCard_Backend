<?php

namespace App\Repositories\CurrencyRepositories;

use App\Models\Currency;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    protected Currency $model;

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
    public function isExistByCode($code)
    {
        return $this->model->where('code', $code)->exists();
    }

    public function getByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function saveNewCurrency(string $name, string $code, string $symbol): void
    {
        $newCurrency = new Currency();
        $newCurrency->name = $name;
        $newCurrency->code = $code;
        $newCurrency->symbol = $symbol;
        $newCurrency->save();
    }

    public function getAllIdCurrencies()
    {
        return $this->model->pluck('id')->toArray();
    }

    public function isExistCurrencyById(int $currencyId)
    {
        return $this->model->where('id', '=', $currencyId)->exists();
    }
}
