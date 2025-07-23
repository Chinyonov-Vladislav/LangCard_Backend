<?php

namespace App\Repositories\TimezoneRepositories;

use App\Models\Timezone;

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

    public function getAllTimezones($namesAttributes)
    {
        $allowedColumns = $this->model->getTableColumns();
        $fields = array_intersect($namesAttributes, $allowedColumns);
        if (empty($fields)) {
            $fields = ['*'];
        }
        logger("Итоговые колонки");
        logger($fields);
        return $this->model->select($fields)->get();
    }
}
