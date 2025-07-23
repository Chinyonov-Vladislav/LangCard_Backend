<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorService
{
    public function __construct()
    {
    }
    public function paginate($query,
                             int $countOnPage = 10,
                             int $page = 1,
                             array $columns = ['*'],
                             string $pageName = 'page'): LengthAwarePaginator
    {
        $page = max(1, $page);
        $countOnPage = max(1, $countOnPage);
        return $query->paginate($countOnPage, $columns, $pageName, $page);
    }

    public function getMetadataForPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'links'=>$paginator->linkCollection()
        ];
    }
}
