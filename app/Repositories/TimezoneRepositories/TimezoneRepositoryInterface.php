<?php

namespace App\Repositories\TimezoneRepositories;

use App\Models\Timezone;

interface TimezoneRepositoryInterface
{
    public function isExistTimezoneByNameRegion(string $nameRegion): bool;
    public function getTimezoneByNameRegion(string $nameRegion): ?Timezone;
    public function getTimezoneById(int $id): ?Timezone;

    public function saveNewTimezone(string $nameRegion, string $offset_UTC);

    public function getAllTimezones($namesAttributes);
}
