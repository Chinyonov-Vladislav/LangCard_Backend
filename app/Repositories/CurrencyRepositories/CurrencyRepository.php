<?php

namespace App\Repositories\CurrencyRepositories;

use App\DTO\DataFromIpGeolocation\CurrencyFromIpGeolocationDTO;
use App\Models\Currency;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    protected Currency $model;

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
    public function isExistByCode($code): bool
    {
        return $this->model->where('code', $code)->exists();
    }

    public function getByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function saveNewCurrency(string $name, string $code, string $symbol): Currency
    {
        $newCurrency = new Currency();
        $newCurrency->name = $name;
        $newCurrency->code = $code;
        $newCurrency->symbol = $symbol;
        $newCurrency->save();
        return $newCurrency;
    }

    public function getAllIdCurrencies(): array
    {
        return $this->model->pluck('id')->toArray();
    }

    public function isExistCurrencyById(int $currencyId): bool
    {
        return $this->model->where('id', '=', $currencyId)->exists();
    }

    public function getCurrencyIdByDataFromApi(?CurrencyFromIpGeolocationDTO $currencyDataDTO): ?int
    {
        if ($currencyDataDTO) {
            if(!$this->isExistByCode($currencyDataDTO->getCode())) {
                $this->saveNewCurrency($currencyDataDTO->getName(), $currencyDataDTO->getCode(), $currencyDataDTO->getSymbol());
            }
            $currencyInfoFromDatabase = $this->getByCode($currencyDataDTO->getCode());
            return $currencyInfoFromDatabase->id;
        }
        return null;
    }
}
