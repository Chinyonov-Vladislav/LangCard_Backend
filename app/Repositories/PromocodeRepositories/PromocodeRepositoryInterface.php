<?php

namespace App\Repositories\PromocodeRepositories;

use App\Models\Promocode;

interface PromocodeRepositoryInterface
{
    public function getActivePromocodesById(?int $tariff_id);
    public function isExistPromocode(string $code): bool;

    public function getPromocodeByCode(string $code): ?Promocode;

    public function deactivatePromocodeByCode(string $code);

    public function deactivatePromocodeByPromocode(Promocode $promocode);
    public function saveNewPromocode(string $code, int $tariff_id);
}
