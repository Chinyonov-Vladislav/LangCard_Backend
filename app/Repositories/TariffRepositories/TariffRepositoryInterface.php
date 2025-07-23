<?php

namespace App\Repositories\TariffRepositories;

use App\Models\Tariff;

interface TariffRepositoryInterface
{
    public function getAllIdActiveTariffs();
    public function getAllIdTariffs();
    public function getActiveTariffsForUserCurrency(int $userCurrencyId);
    public function getAllActiveTariffs();
    public function isExistTariff(string $name, int $days);
    public function isExistTariffById(int $tariffId);
    public function saveNewTariff(string $name, int $days, bool $statusActive = true): ?Tariff;
    public function changeStatus($tariffId);


}
