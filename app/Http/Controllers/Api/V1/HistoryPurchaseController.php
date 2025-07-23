<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HistoryPurchaseRequests\GetHistoryPurchaseRequest;
use App\Http\Resources\v1\HistoryPurchaseResources\HistoryPurchaseResource;
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
    #[QueryParameter('page', 'Номер страницы', type: 'int',default:10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int',default:10, example: 10)]
    public function getHistoryPurchasesOfAuthUser(PaginatorService $paginator, GetHistoryPurchaseRequest $request)
    {
        $authUserId = auth()->id();
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->historyPurchaseRepository->getHistoryPurchasesOfAuthUser($paginator, $authUserId, $countOnPage, $numberCurrentPage);
        return ApiResponse::success(__('api.purchase_history_retrieved', ['authUserId'=>$authUserId]), (object)['items'=>HistoryPurchaseResource::collection($data['items']),
            'pagination' => $data['pagination']]);
    }
}
