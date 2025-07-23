<?php

namespace App\Repositories\HistoryPurchasesRepository;

use App\Models\HistoryPurchase;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

class HistoryPurchaseRepository implements HistoryPurchaseRepositoryInterface
{
    protected HistoryPurchase $model;

    public function __construct(HistoryPurchase $model)
    {
        $this->model = $model;
    }

    public function saveNewHistoryPurchase(string $datePurchase, string $dateEnd, int $userId, int $costId)
    {
        $newHistoryPurchase = new HistoryPurchase();
        $newHistoryPurchase->date_purchase = $datePurchase;
        $newHistoryPurchase->date_end = $dateEnd;
        $newHistoryPurchase->user_id = $userId;
        $newHistoryPurchase->cost_id = $costId;
        $newHistoryPurchase->save();
    }

    public function getHistoryPurchasesOfAuthUser(PaginatorService $paginatorService, int $authUserId, int $countOnPage, int $page): array
    {
        $query = $this->model->with(['cost'=>function ($query) {
            $query->with(['tariff', 'currency']);
        }])->where('user_id','=', $authUserId)->orderBy('created_at', 'desc');
        $data = $paginatorService->paginate($query, $countOnPage, $page);
        return ['items' => collect($data->items()), "pagination" => $paginatorService->getMetadataForPagination($data)];
    }
}
