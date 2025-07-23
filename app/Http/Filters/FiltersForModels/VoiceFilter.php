<?php

namespace App\Http\Filters\FiltersForModels;

use App\Http\Filters\QueryFilter;

class VoiceFilter extends QueryFilter
{
    public function languages($language): void
    {
        $array_params = array_filter(explode(',', $language));
        $this->builder->whereHas('language', function ($query) use ($array_params) {
            $query->whereIn('locale', $array_params);
        });
    }
}
