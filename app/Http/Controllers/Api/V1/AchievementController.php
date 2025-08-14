<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AchievementRequests\AchievementFilterRequest;
use App\Http\Resources\V1\AchievementResources\AchievementResource;
use App\Http\Resources\V1\DeckResources\DeckResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\AchievementRepositories\AchievementRepositoryInterface;
use App\Services\PaginatorService;

class AchievementController extends Controller
{
    protected AchievementRepositoryInterface $achievementRepository;

    public function __construct(AchievementRepositoryInterface $achievementRepository)
    {
        $this->achievementRepository = $achievementRepository;
    }


    /**
     * @OA\Get(
     *     path="/achievements",
     *     summary="Получение достижений пользователя с прогрессом",
     *     description="Возвращает список достижений текущего авторизованного пользователя с указанием прогресса и датой разблокировки (если есть), а также информацию о пагинации.",
     *     operationId="getAchievements",
     *     tags={"Достижения"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Номер страницы (минимум 1)",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         required=false,
     *         description="Количество элементов на странице (минимум 1)",
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение достижений",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о достижениях на странице №1"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 required = {"items","pagination"},
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/AchievementResource")
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     ref="#/components/schemas/PaginationResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function getAchievements(AchievementFilterRequest $request, PaginatorService $paginator)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->achievementRepository->getAchievementsForUserWithProgressAndPagination(auth()->id(), $paginator, $countOnPage, $numberCurrentPage);
        return ApiResponse::success("Данные о достижениях на странице №".$countOnPage, (object)['items' => AchievementResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }
}
