<?php

namespace App\Repositories\CostRepositories;

interface CostRepositoryInterface
{
    public function isExistByTariffIdAndCurrencyId(int $tariffId, int $currencyId);
    public function saveNewCost(float $cost, int $tariffId, int $currencyId, bool $isActive);

    public function getAllCostsWithActiveTariffByCurrencyId(int $currencyId);

}
