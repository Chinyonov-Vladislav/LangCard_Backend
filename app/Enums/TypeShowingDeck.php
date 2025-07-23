<?php

namespace App\Enums;

enum TypeShowingDeck: string
{
    case onlyPremium = 'onlyPremium';
    case onlyNotPremium = 'onlyNotPremium';
    case all = 'all';

    public static function getValues(): array
    {
        return ['onlyPremium', 'onlyNotPremium', 'all'];
    }

}
