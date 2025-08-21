<?php

namespace App\Http\Filters\FiltersForModels;

use App\Enums\TypeShowingDeck;
use App\Http\Filters\QueryFilter;

class DeckFilter extends QueryFilter
{
    public function originalLanguages($originalLanguages): void
    {
        $array_params = array_filter(explode(',', $originalLanguages));
        $this->builder->whereHas('originalLanguage', function ($query) use ($array_params) {
            $query->whereIn('locale', $array_params);
        });
    }

    public function targetLanguages($targetLanguages): void
    {
        $array_params = array_filter(explode(',', $targetLanguages));
        $this->builder->whereHas('targetLanguage', function ($query) use ($array_params) {
            $query->whereIn('locale', $array_params);
        });
    }

    public function showPremium($typePremium): void
    {
        switch ($typePremium) {
            case TypeShowingDeck::onlyPremium->value:
                $this->builder->where('is_premium', '=',true);
                break;
            case TypeShowingDeck::onlyNotPremium->value:
                $this->builder->where('is_premium', '=',false);
        }
    }

}
