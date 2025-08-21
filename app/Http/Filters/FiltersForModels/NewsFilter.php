<?php

namespace App\Http\Filters\FiltersForModels;

use App\Http\Filters\QueryFilter;

class NewsFilter extends QueryFilter
{
    public function orderDirection(string $direction): void
    {
        $this->builder->orderBy("published_at", $direction);
    }
}
