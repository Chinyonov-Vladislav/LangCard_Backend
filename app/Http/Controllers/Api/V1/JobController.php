<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\JobRequests\JobFilterRequest;
use App\Http\Resources\V1\JobResources\JobResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\JobStatusRepositories\JobStatusRepository;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Services\PaginatorService;

class JobController extends Controller
{
    protected JobStatusRepositoryInterface $jobStatusRepository;

    public function __construct(JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->jobStatusRepository = $jobStatusRepository;
    }

    public function getJobsOfAuthUser(JobFilterRequest $request, PaginatorService $paginator)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $jobsInfoArray = $this->jobStatusRepository->getJobsOfUserWithPagination($paginator, auth()->id(), $countOnPage, $numberCurrentPage);
        return ApiResponse::success("Jobs для авторизованного пользователя", (object)['items'=>JobResource::collection($jobsInfoArray['items']),
            'pagination' => new PaginationResource($jobsInfoArray['pagination'])] );
    }
}
