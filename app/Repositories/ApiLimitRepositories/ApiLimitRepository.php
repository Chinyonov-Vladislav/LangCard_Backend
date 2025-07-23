<?php

namespace App\Repositories\ApiLimitRepositories;

use App\Models\ApiLimit;
use Carbon\Carbon;

class ApiLimitRepository implements ApiLimitRepositoryInterface
{
    protected ApiLimit $model;
    public function __construct(ApiLimit $apiLimit)
    {
        $this->model = $apiLimit;
    }
    public function findOrCreateByDate(string $date)
    {
        return $this->model->firstOrCreate(['day' => $date]);
    }

    public function incrementRequestCount(ApiLimit $apiLimit)
    {
        $apiLimit->increment('request_count');
    }

    public function deleteInfoBeforeCurrentDay()
    {
        $this->model->where('day', '<', Carbon::today())->delete();
    }

    public function getMinDay() : ?string
    {
        return $this->model->min('day');
    }

    public function getMaxDay() : ?string
    {
        return $this->model->max('day');
    }

    public function getMinRequestCount() : ?int
    {
        return $this->model->min('request_count');
    }

    public function getMaxRequestCount() : ?int
    {
        return $this->model->max('request_count');
    }
}
