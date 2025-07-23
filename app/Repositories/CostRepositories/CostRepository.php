<?php

namespace App\Repositories\CostRepositories;

use App\Models\Cost;
use Illuminate\Database\Eloquent\Collection;

class CostRepository implements CostRepositoryInterface
{
    protected Cost $model;

    public function __construct(Cost $model)
    {
        $this->model = $model;
    }

    public function isExistByTariffIdAndCurrencyId(int $tariffId, int $currencyId)
    {
        return $this->model->where('tariff_id','=', $tariffId)->where('currency_id','=',$currencyId)->exists();
    }

    public function saveNewCost(float $cost, int $tariffId, int $currencyId, bool $isActive)
    {
        $newCost = new Cost();
        $newCost->cost = $cost;
        $newCost->currency_id = $currencyId;
        $newCost->tariff_id = $tariffId;
        $newCost->is_active = $isActive;
        $newCost->save();
    }

    public function getAllCostsWithActiveTariffByCurrencyId(int $currencyId): Collection
    {
        return $this->model->with(['tariff'=>function ($query) {
            $query->where('is_active','=',true);
        }])->where('currency_id','=',$currencyId)->get();
    }
}
