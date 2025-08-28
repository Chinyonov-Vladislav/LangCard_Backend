<?php

namespace App\Repositories\NewsRepositories;

use App\Http\Filters\FiltersForModels\NewsFilter;
use App\Models\News;
use App\Services\PaginatorService;

interface NewsRepositoryInterface
{
    public function getNewsWithPagination(PaginatorService $paginator, NewsFilter $newsFilter,  int $countOnPage, int $numberCurrentPage);

    public function getNewsById(int $id): ?News;

    public function updateNewsByIdNews(int $newsId, string $title, ?string $mainImage, array $content, ?string $publishedAt = null);

    public function updateNewsByNewsObject(News $news, string $title, ?string $mainImage, string $content, ?string $publishedAt = null): News;

    public function saveNews(string $title, ?string $mainImage, string $content,int $userId, ?string $publishedAt = null): News;

    public function deleteNews(News $news);
    public function deleteNewsById(int $id);
}
