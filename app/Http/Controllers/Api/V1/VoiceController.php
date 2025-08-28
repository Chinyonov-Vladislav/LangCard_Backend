<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatuses;
use App\Enums\NameJobsEnum;
use App\Enums\TypeStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\FiltersForModels\VoiceFilter;
use App\Http\Requests\Api\V1\VoiceRequests\GetVoicesRequest;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Resources\v1\VoiceResources\VoiceResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\FetchVoicesFromFreetts;
use App\Jobs\SyncVoiceStatusesFromFreetts;
use App\Models\JobStatus;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use App\Services\PaginatorService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Str;

class VoiceController extends Controller
{
    protected VoiceRepositoryInterface $voiceRepository;
    protected LanguageRepositoryInterface $languageRepository;

    protected JobStatusRepositoryInterface $jobStatusRepository;

    public function __construct(VoiceRepositoryInterface $voiceRepository, LanguageRepositoryInterface $languageRepository, JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->voiceRepository = $voiceRepository;
        $this->languageRepository = $languageRepository;
        $this->jobStatusRepository = $jobStatusRepository;
    }

    /**
     * @OA\Get(
     *     path="/voices",
     *     operationId="getInfoVoices",
     *     tags={"Голоса"},
     *     summary="Получение информации о допустимых голосах для озвучки в системе",
     *     description="Получение информации о допустимых голосах для озвучки в системе",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Номер страницы",
     *          required=false,
     *          @OA\Schema(type="integer", minimum=1, example=1)
     *      ),
     *      @OA\Parameter(
     *          name="countOnPage",
     *          in="query",
     *          description="Количество элементов на странице",
     *          required=false,
     *          @OA\Schema(type="integer", minimum=1, example=10)
     *      ),
     *      @OA\Parameter(
     *           name="languages",
     *           in="query",
     *           description="Параметр для фильтрации по языкам (коды языков через запятую)",
     *           required=false,
     *           @OA\Schema(type="string", example="en_US,ru_RU,de_DE")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Полученные данные о голосах для озвучки",
     *          @OA\JsonContent(
     *              type="object",
     *              required = {"status","message","data"},
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Данные колод на странице 1"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="items",
     *                      type="array",
     *                      @OA\Items(ref="#/components/schemas/DeckResource")
     *                  ),
     *                  @OA\Property(
     *                       property="pagination",
     *                       ref="#/components/schemas/PaginationResource"
     *                   )
     *              )
     *          )
     *       ),
     *       @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *       @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     **/
    #[QueryParameter('page', 'Номер страницы', type: 'int', default: 10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int', default: 10, example: 10)]
    #[QueryParameter('languages', description: 'Параметр для фильтрации по языкам', type: 'string', infer: true, example: 'en_US,ru_RU,de_DE')]
    public function getVoices(PaginatorService $paginator, VoiceFilter $voiceFilter, GetVoicesRequest $request)
    {
        $useZeroDefaults = !$request->has('countOnPage') && !$request->has('page');
        $countOnPage = (int)$request->input('countOnPage',
            $useZeroDefaults ? config('app.zero_count_on_page') : config('app.default_count_on_page')
        );
        $numberCurrentPage = (int)$request->input('page',
            $useZeroDefaults ? config('app.zero_page') : config('app.default_page')
        );
        $data = $this->voiceRepository->getVoicesWithPaginationAndFilters($paginator, $voiceFilter, $countOnPage, $numberCurrentPage);
        if ($useZeroDefaults) {
            return ApiResponse::success('Данные о голосах для озвучивания текста', (object)['items' => VoiceResource::collection($data)]);
        }
        return ApiResponse::success("Данные о поддерживаемых голосах для языков на странице $numberCurrentPage", (object)['items' => VoiceResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }
    /**
     * @OA\Post(
     *     path="/voices",
     *     operationId="createVoice",
     *     tags={"Голоса"},
     *     summary="Запустить обновление голосов",
     *     description="Ставит задачу на обновление голосов в очередь. Доступно только администраторам.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Задача поставлена в очередь",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Задача на обновление голосов поставлена в очередь"),
     *             @OA\Property(property="data", type="object",nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function createVoice()
    {
        $jobId = (string) Str::uuid();
        $this->jobStatusRepository->saveNewJobStatus($jobId, NameJobsEnum::FetchVoicesFromFreetts->value, JobStatuses::queued->value, auth()->id());
        FetchVoicesFromFreetts::dispatch($jobId);
        return ApiResponse::success('Задача на получение голосов поставлена в очередь',(object)["job_id" => $jobId]);
    }

    /**
     * @OA\Patch(
     *     path="/voices",
     *     operationId="updateStatusOfVoices",
     *     tags={"Голоса"},
     *     summary="Синхронизировать статусы голосов",
     *     description="Запускает процесс синхронизации статусов голосов из FreeTTS. Доступно только администраторам.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Синхронизация запущена",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Синхронизация статусов голосов запущена"),
     *             @OA\Property(property="data", type="object",nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function updateStatusOfVoices()
    {
        $jobId = (string) Str::uuid();
        $this->jobStatusRepository->saveNewJobStatus($jobId, NameJobsEnum::SyncVoiceStatusesFromFreetts->value, JobStatuses::queued->value, auth()->id());
        SyncVoiceStatusesFromFreetts::dispatch($jobId);
        return ApiResponse::success('Синхронизация статусов голосов запущена', (object)["job_id" => $jobId]);
    }
}
