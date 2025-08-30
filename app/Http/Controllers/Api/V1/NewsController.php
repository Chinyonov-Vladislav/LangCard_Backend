<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatuses;
use App\Enums\NameJobsEnum;
use App\Http\Controllers\Controller;
use App\Http\Filters\FiltersForModels\NewsFilter;
use App\Http\Requests\Api\V1\NewsRequests\CreateNewsRequest;
use App\Http\Requests\Api\V1\NewsRequests\NewsFilterRequest;
use App\Http\Requests\Api\V1\NewsRequests\UpdateNewsRequest;
use App\Http\Resources\V1\NewsResources\FullNewsResource;
use App\Http\Resources\V1\NewsResources\ShortNewsResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\SendNewsMailJob;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\NewsRepositories\NewsRepositoryInterface;
use App\Services\PaginatorService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    protected JobStatusRepositoryInterface $jobStatusRepository;
    protected NewsRepositoryInterface $newsRepository;

    public function __construct(NewsRepositoryInterface $newsRepository, JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->newsRepository = $newsRepository;
        $this->jobStatusRepository = $jobStatusRepository;
    }

    public function getNews(NewsFilterRequest $request, PaginatorService $paginator, NewsFilter $filter)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->newsRepository->getNewsWithPagination($paginator, $filter, $countOnPage, $numberCurrentPage);
        return ApiResponse::success("Новости на странице №$numberCurrentPage", (object)['items' => ShortNewsResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }

    public function getNewsById(int $id)
    {
        $newsById = $this->newsRepository->getNewsById($id);
        if ($newsById === null) {
            return ApiResponse::error("Новость с id = $id не найдена", null, 404);
        }
        return ApiResponse::success("Новость с id = $id", (object)["item" => new FullNewsResource($newsById)]);
    }

    public function addNews(CreateNewsRequest $request)
    {
        $newItemNews = $this->newsRepository->saveNews($request->title, $request->main_image, $request->content_news, auth()->id(), $request->published_at);
        $publishedDate = Carbon::parse($newItemNews->published_at);
        $jobId = (string)Str::uuid();
        $this->jobStatusRepository->saveNewJobStatus($jobId, NameJobsEnum::SendNewsMailJob->value, JobStatuses::queued->value, auth()->id(), ["news_id" => $jobId]);
        if ($publishedDate->isFuture()) {
            SendNewsMailJob::dispatch($jobId, $newItemNews->id)->delay($publishedDate);
            return ApiResponse::success("Новая новость была успешно сохранена. Рассылка сообщения о публикации новости начнётся в указанное время", null, 201);
        }
        SendNewsMailJob::dispatch($jobId, $newItemNews->id);
        return ApiResponse::success("Новая новость была успешно сохранена. Рассылка сообщения о публикации новости начата", null, 201);

    }

    public function updateNews(int $id, UpdateNewsRequest $request)
    {
        $news = $this->newsRepository->getNewsById($id);
        if ($news === null) {
            return ApiResponse::error("Новость с id = $id не найдена", null, 404);
        }
        if ($news->user_id !== auth()->id()) {
            return ApiResponse::error("Авторизованный пользователь не может выполнить редактирование новости, так как не является её автором", null, 409);
        }
        $updatedNews = $this->newsRepository->updateNewsByNewsObject($news, $request->title, $request->main_image, $request->content_news, $request->published_at);
        $job = $this->jobStatusRepository->getJobForNews($id);
        if ($job !== null && $job->status === JobStatuses::queued->value) {
            $this->jobStatusRepository->updateStatus($job->job_id, JobStatuses::cancelled->value); // отменяем публикацию информации о job в прежнее время
            $newPublishedDate = Carbon::parse($updatedNews->published_at);
            $jobId = (string)Str::uuid();
            $this->jobStatusRepository->saveNewJobStatus($jobId, NameJobsEnum::SendNewsMailJob->value, JobStatuses::queued->value, auth()->id(), ["news_id" => $jobId]);
            if ($newPublishedDate->isFuture()) {
                SendNewsMailJob::dispatch($jobId, $updatedNews->id)->delay($newPublishedDate);
                return ApiResponse::success("Новость была успешно отредактирована. Рассылка сообщения о публикации новости начнётся в указанное время", null, 201);
            }
            SendNewsMailJob::dispatch($jobId, $updatedNews->id);
            return ApiResponse::success("Новость была успешно отредактирована. Рассылка сообщения о публикации новости начата", null, 201);
        }
        return ApiResponse::success("Новость была успешно отредактирована");
    }

    public function deleteNews(int $id)
    {
        $news = $this->newsRepository->getNewsById($id);
        if ($news === null) {
            return ApiResponse::error("Новость с id = $id не найдена", null, 404);
        }
        if ($news->user->id !== auth()->id()) {
            return ApiResponse::error("Авторизованный пользователь не является автором новости, поэтому не может её удалить", null, 409);
        }
        $this->newsRepository->deleteNews($news);
        return ApiResponse::success("Новость была успешно удалена");
    }
}
