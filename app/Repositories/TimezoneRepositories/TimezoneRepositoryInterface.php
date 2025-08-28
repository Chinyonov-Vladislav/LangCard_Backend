<?php

namespace App\Repositories\TimezoneRepositories;

use App\DTO\DataFromIpGeolocation\TimezoneFromIpGeolocationDTO;
use App\Models\Timezone;
use App\Services\PaginatorService;

interface TimezoneRepositoryInterface
{
    public function isExistTimezoneByNameRegion(string $nameRegion): bool;
    public function getTimezoneByNameRegion(string $nameRegion): ?Timezone;
    public function getTimezoneById(int $id): ?Timezone;

    public function saveNewTimezone(string $nameRegion, string $offset_UTC);

    public function getAllTimezones($namesAttributes);

    public function getTimezoneWithPagination(PaginatorService $paginator, array $namesAttributes, int $currentPage, int $countOnPage);

    public function getTimezoneIdByDataFromApi(TimezoneFromIpGeolocationDTO $timezoneFromIpGeolocationDTO): ?int;
}
