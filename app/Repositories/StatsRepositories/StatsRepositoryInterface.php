<?php

namespace App\Repositories\StatsRepositories;

use Illuminate\Support\Collection;

interface StatsRepositoryInterface
{
    public function getCountUsersByMonth($startDate, $endDate): Collection;

    public function getTopicsWithCountDecksAndPercentage();
}
