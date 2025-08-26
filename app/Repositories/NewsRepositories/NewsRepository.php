<?php

namespace App\Repositories\NewsRepositories;

use App\Http\Filters\FiltersForModels\NewsFilter;
use App\Models\News;
use App\Services\PaginatorService;
use Carbon\Carbon;

class NewsRepository implements NewsRepositoryInterface
{
    protected News $model;

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    public function getNewsWithPagination(PaginatorService $paginator, NewsFilter $newsFilter, int $countOnPage, int $numberCurrentPage): array
    {
        $query = $this->model->select(["title", "main_image", "published_at", "user_id"])->with(['user' => function ($query) {
            $query->select(["id", "name", "avatar_url"]);
        }])->where('published_at', '<', Carbon::now())->filter($newsFilter);
        $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function getNewsById(int $id): ?News
    {
        return $this->model->with(["user"])->first();
    }

    public function saveNews(string $title, ?string $mainImage, array $content, int $userId, ?string $publishedAt = null): News
    {
        $newItemNews = new News();
        $newItemNews->title = $title;
        $newItemNews->main_image = $mainImage;
        $newItemNews->content = $content;
        $newItemNews->published_at = $publishedAt === null ? Carbon::now() : $publishedAt;
        $newItemNews->user_id = $userId;
        $newItemNews->save();
        return $newItemNews;
    }

    public function deleteNews(News $news): void
    {
        $news->delete();
    }

    public function deleteNewsById(int $id): void
    {
        $this->model->where("id", "=",$id)->delete();
    }

    public function updateNewsByIdNews(int $newsId, string $title, ?string $mainImage, array $content, ?string $publishedAt = null)
    {
        $this->model->where("id", '=',$newsId)->update([
            "title"=>$title,
            "main_image"=>$mainImage,
            "content"=>$content,
            "published_at"=>$publishedAt === null ? Carbon::now() : $publishedAt,
        ]);
    }

    public function updateNewsByNewsObject(News $news, string $title, ?string $mainImage, array $content, ?string $publishedAt = null): News
    {
        $news->title = $title;
        $news->main_image = $mainImage;
        $news->content = $content;
        $news->published_at = $publishedAt === null ? Carbon::now() : $publishedAt;
        $news->save();
        return $news;
    }
}
