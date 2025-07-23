<?php

namespace App\Http\Filters\FiltersForModels;

use App\Http\Filters\QueryFilter;

class TimezoneFilter extends QueryFilter
{
    public function names(string $names): void
    {
        $array_params = array_filter(explode(',', $names));
        $this->builder->whereIn('name', $array_params);
    }
    public function offset_utcs(string $offset_utcs): void
    {
        $array_params = array_filter(explode(',', $offset_utcs));
        $this->builder->whereIn('offset_utc', $array_params);
    }
}
