<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StatsRequests\DatesForCountUsersByMonthsRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\StatsRepositories\StatsRepositoryInterface;

class StatsController extends Controller
{
    protected StatsRepositoryInterface $statsRepository;

    public function __construct(StatsRepositoryInterface $statsRepository)
    {
        $this->statsRepository = $statsRepository;
    }

    public function getCountUsersByMonths(DatesForCountUsersByMonthsRequest $request)
    {
        $result = $this->statsRepository->getCountUsersByMonth($request->getStartMonth(), $request->getEndMonth());

        return ApiResponse::success("Количество пользователей, зарегистрировавшихся в системе по месяцам", (object)['countUsersByMonths' => $result]);
    }
    public function getTopicsWithCountDecksAndPercentage()
    {
        $result = $this->statsRepository->getTopicsWithCountDecksAndPercentage();
        return ApiResponse::success('Количество колод по топикам с процентным соотношением', (object)['topicsWithCountDecksAndPercentage' => $result]);
    }
}
