<?php

namespace App\Repositories\TimezoneRepositories;

use App\DTO\DataFromIpGeolocation\TimezoneFromIpGeolocationDTO;
use App\Models\Timezone;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

class TimezoneRepository implements TimezoneRepositoryInterface
{
    protected TimeZone $model;

    public function __construct(TimeZone $model)
    {
        $this->model = $model;
    }

    public function isExistTimezoneByNameRegion(string $nameRegion): bool
    {
        return $this->model->where('name', '=', $nameRegion)->exists();
    }

    public function getTimezoneByNameRegion(string $nameRegion): ?Timezone
    {
        return $this->model->where('name', '=', $nameRegion)->first();
    }

    public function getTimezoneById(int $id): ?Timezone
    {
        return $this->model->where('id', '=', $id)->first();
    }

    public function saveNewTimezone(string $nameRegion, string $offset_UTC): void
    {
        $newTimezone = new Timezone();
        $newTimezone->name = $nameRegion;
        $newTimezone->offset_utc = $offset_UTC;
        $newTimezone->save();
    }

    public function getAllTimezones($namesAttributes): Collection
    {
        $allowedColumns = $this->model->getTableColumns();
        $fields = array_intersect($namesAttributes, $allowedColumns);
        if (empty($fields)) {
            $fields = ['*'];
        }
        return $this->model->select($fields)->get();
    }

    public function getTimezoneWithPagination(PaginatorService $paginator, array $namesAttributes, int $currentPage, int $countOnPage): array
    {
        $allowedColumns = $this->model->getTableColumns();
        $fields = array_intersect($namesAttributes, $allowedColumns);
        if (empty($fields)) {
            $fields = ['*'];
        }
        $query = $this->model->select($fields);
        $data = $paginator->paginate($query, $countOnPage, $currentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function getTimezoneIdByDataFromApi(TimezoneFromIpGeolocationDTO $timezoneFromIpGeolocationDTO): ?int
    {
        if ($timezoneFromIpGeolocationDTO->getTimezoneName() && $this->isExistTimezoneByNameRegion($timezoneFromIpGeolocationDTO->getTimezoneName())) {
            $timezoneDB = $this->getTimezoneByNameRegion($timezoneFromIpGeolocationDTO->getTimezoneName());
            return $timezoneDB->id;
        }
        return null;
    }
}
