<?php

namespace App\Repositories\HistoryPurchasesRepository;

use App\Services\PaginatorService;
use Ramsey\Collection\Collection;

interface HistoryPurchaseRepositoryInterface
{
    public function getHistoryPurchasesOfAuthUser(PaginatorService $paginatorService, int $authUserId, int $countOnPage, int $page): array;
    public function saveNewHistoryPurchase(string $datePurchase,string $dateEnd, int $userId, int $costId);
}
