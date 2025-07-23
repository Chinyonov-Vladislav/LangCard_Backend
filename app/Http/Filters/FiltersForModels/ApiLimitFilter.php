<?php

namespace App\Http\Filters\FiltersForModels;

use App\Helpers\ValidationDateFromString;
use App\Http\Filters\QueryFilter;
use Illuminate\Http\Request;

class ApiLimitFilter extends QueryFilter
{
    private ValidationDateFromString $validation;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->validation = new ValidationDateFromString();
    }

    public function day(string $date): void
    {
        if($this->validation->validate($date)) {
            $this->builder->whereDate('day', $date);
        }
    }
    // Фильтрация по началу периода (включительно)
    public function fromDay(string $date): void
    {
        if($this->validation->validate($date)) {
            $this->builder->whereDate('day', '>=', $date);
        }
    }

    // Фильтрация по концу периода (включительно)
    public function toDay(string $date): void
    {
        if($this->validation->validate($date)) {
            $this->builder->whereDate('day', '<=', $date);
        }
    }

    // Конкретное значение
    public function requestCount(int|string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false)
        {
            $this->builder->where('request_count', '=', (int)$value);
        }
    }

    // Минимальное значение
    public function minRequestCount(int|string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false)
        {
            $this->builder->where('request_count', '>=', (int)$value);
        }

    }

    // Максимальное значение
    public function maxRequestCount(int|string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false)
        {
            $this->builder->where('request_count', '<=', (int)$value);
        }
    }

}
