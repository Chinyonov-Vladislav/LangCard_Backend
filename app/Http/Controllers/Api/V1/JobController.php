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

    /**
     * @OA\Get(
     *     path="/jobs",
     *     operationId="getJobsOfAuthUser",
     *     tags={"Jobs"},
     *     summary="Получить список задач (Jobs) авторизованного пользователя",
     *     description="Возвращает список записей о задачах (Jobs), инициированных авторизованным пользователем, с возможностью пагинации.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы (необязательный параметр, по умолчанию 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         description="Количество элементов на странице (необязательный параметр, по умолчанию 10)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список задач пользователя с пагинацией",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status", "message", "data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Jobs для авторизованного пользователя"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/JobResource")
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     ref="#/components/schemas/PaginationResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function getJobsOfAuthUser(JobFilterRequest $request, PaginatorService $paginator)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $jobsInfoArray = $this->jobStatusRepository->getJobsOfUserWithPagination($paginator, auth()->id(), $countOnPage, $numberCurrentPage);
        return ApiResponse::success("Jobs для авторизованного пользователя", (object)['items'=>JobResource::collection($jobsInfoArray['items']),
            'pagination' => new PaginationResource($jobsInfoArray['pagination'])] );
    }
}
