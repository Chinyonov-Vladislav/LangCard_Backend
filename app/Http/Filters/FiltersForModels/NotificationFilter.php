<?php

namespace App\Http\Filters\FiltersForModels;

use App\Http\Filters\QueryFilter;

class NotificationFilter extends QueryFilter
{
    public function onlyUnread(bool $onlyUnread): void
    {
        if($onlyUnread)
        {
            $this->builder->where('read_at', null);
        }
    }
    public function orderDirection(string $orderDirection): void
    {
        $this->builder->orderBy("created_at", $orderDirection);
    }
}
