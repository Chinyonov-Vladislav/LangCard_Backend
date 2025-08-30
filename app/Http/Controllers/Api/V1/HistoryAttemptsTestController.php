<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HistoryAttemptRequests\GetHistoryAttemptsRequest;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Resources\v1\UserTestResultResources\UserTestResultResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use App\Services\PaginatorService;
use Dedoc\Scramble\Attributes\QueryParameter;

class HistoryAttemptsTestController extends Controller
{
    protected UserTestResultRepositoryInterface $userTestResultRepository;
    public function __construct(UserTestResultRepositoryInterface $userTestResultRepository)
    {
        $this->userTestResultRepository = $userTestResultRepository;
    }
    /**
     * @OA\Get(
     *     path="/historyAttempts",
     *     summary="Получить историю попыток прохождения тестов",
     *     description="Возвращает пагинированный список попыток прохождения тестов текущего авторизованного пользователя.",
     *     operationId="getAttemptsTests",
     *     tags={"История попыток прохождения тестов"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Номер страницы",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         required=false,
     *         description="Количество элементов на странице",
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список попыток получен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о попытках на странице 1"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/UserTestResultResource")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/PaginationResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    #[QueryParameter('page', 'Номер страницы', type: 'int',default:10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int',default:10, example: 10)]
    public function getAttemptsTests(PaginatorService $paginator, GetHistoryAttemptsRequest $request)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->userTestResultRepository->getResultAttemptsOfCurrentUserWithPagination($paginator, auth()->id(), $numberCurrentPage, $countOnPage);
        return ApiResponse::success(__('api.attempts_data_on_page', ['numberCurrentPage'=>$numberCurrentPage]), (object)['items'=>UserTestResultResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }
}
