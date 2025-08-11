<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StatsRequests\DatesForCountUsersByMonthsRequest;
use App\Http\Resources\V1\StatsResources\CountUsersByMonthsResource;
use App\Http\Resources\V1\StatsResources\TopicWithDecksCountResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\StatsRepositories\StatsRepositoryInterface;

class StatsController extends Controller
{
    protected StatsRepositoryInterface $statsRepository;

    public function __construct(StatsRepositoryInterface $statsRepository)
    {
        $this->statsRepository = $statsRepository;
    }

    /**
     * @OA\Get(
     *     path="/stats/countUsersByMonths",
     *     summary="Получение количества пользователей по месяцам",
     *     tags={"Статистика"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="start_month",
     *         in="query",
     *         description="Начальный месяц в формате YYYY-MM",
     *         required=false,
     *         @OA\Schema(type="string", pattern="^\d{4}-(0[1-9]|1[0-2])$", example="2024-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_month",
     *         in="query",
     *         description="Конечный месяц в формате YYYY-MM",
     *         required=false,
     *         @OA\Schema(type="string", pattern="^\d{4}-(0[1-9]|1[0-2])$", example="2024-08")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с количеством пользователей по месяцам",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Количество пользователей, зарегистрировавшихся в системе по месяцам"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="countUsersByMonths",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CountUsersByMonthsResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *            response=422,
     *            description="Ошибка валидации",
     *            @OA\JsonContent(
     *                @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                @OA\Property(property="errors", type="object",
     *                    @OA\Property(property="start_month", type="array",
     *                        @OA\Items(type="string", example="The field 'Start month' must be in YYYY-MM format")
     *                    ),
     *                    @OA\Property(property="end_month", type="array",
     *                         @OA\Items(type="string", example="The field 'End month' must be in YYYY-MM format")
     *                     )
     *                )
     *            )
     *        ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *      @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function getCountUsersByMonths(DatesForCountUsersByMonthsRequest $request)
    {
        $result = $this->statsRepository->getCountUsersByMonth($request->getStartMonth(), $request->getEndMonth());

        return ApiResponse::success("Количество пользователей, зарегистрировавшихся в системе по месяцам", (object)['countUsersByMonths' => CountUsersByMonthsResource::collection($result)]);
    }

    /**
     * @OA\Get(
     *     path="/stats/countDecksByTopic",
     *     operationId="getTopicsWithCountDecksAndPercentage",
     *     summary="Получить количество активных колод по тематикам с процентным соотношением",
     *     description="Возвращает список тем с количеством колод и их процентом от общего количества колод. Доступно только администраторам.",
     *     tags={"Статистика"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с данными статистики по темам",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Количество колод по топикам с процентным соотношением"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="topicsWithCountDecksAndPercentage",
     *                     type="array",
     *                     description="Массив тем с количеством колод и процентом",
     *                     @OA\Items(ref="#/components/schemas/TopicWithDecksCountResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function getTopicsWithCountDecksAndPercentage()
    {
        $result = $this->statsRepository->getTopicsWithCountDecksAndPercentage();
        return ApiResponse::success('Количество колод по топикам с процентным соотношением', (object)['topicsWithCountDecksAndPercentage' => TopicWithDecksCountResource::collection($result)]);
    }
}
