<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HistoryPurchaseRequests\GetHistoryPurchaseRequest;
use App\Http\Resources\V1\HistoryPurchaseResources\HistoryPurchaseResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\HistoryPurchasesRepository\HistoryPurchaseRepositoryInterface;
use App\Services\PaginatorService;
use Dedoc\Scramble\Attributes\QueryParameter;

class HistoryPurchaseController extends Controller
{
    protected HistoryPurchaseRepositoryInterface $historyPurchaseRepository;

    public function __construct(HistoryPurchaseRepositoryInterface $historyPurchaseRepository)
    {
        $this->historyPurchaseRepository = $historyPurchaseRepository;
    }




    /**
     * @OA\Get(
     *     path="/historyPurchases",
     *     summary="Получить историю покупок авторизованного пользователя",
     *     tags={"История покупок"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы для пагинации",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с историей покупок и пагинацией",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="История покупок пользователя (ID: 5) успешно получена."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 required={"items", "pagination"},
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/HistoryPurchaseResource")
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     ref="#/components/schemas/PaginationResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    #[QueryParameter('page', 'Номер страницы', type: 'int',default:10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int',default:10, example: 10)]
    public function getHistoryPurchasesOfAuthUser(PaginatorService $paginator, GetHistoryPurchaseRequest $request)
    {
        $authUserId = auth()->id();
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->historyPurchaseRepository->getHistoryPurchasesOfAuthUser($paginator, $authUserId, $countOnPage, $numberCurrentPage);
        return ApiResponse::success(__('api.purchase_history_retrieved', ['authUserId'=>$authUserId]), (object)['items'=>HistoryPurchaseResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }
}
