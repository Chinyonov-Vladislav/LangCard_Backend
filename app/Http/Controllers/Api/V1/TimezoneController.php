<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TimezoneRequests\TimezoneFilterRequest;
use App\Http\Resources\V1\TimezoneResources\TimezoneResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use App\Services\PaginatorService;
use Illuminate\Http\Request;
class TimezoneController extends Controller
{
    protected TimezoneRepositoryInterface $timezoneRepository;
    public function __construct(TimezoneRepositoryInterface $timezoneRepository)
    {
        $this->timezoneRepository = $timezoneRepository;
    }
    /**
     * @OA\Get(
     *     path="/timezones",
     *     operationId="getTimezones",
     *     summary="Получить список часовых поясов с пагинацией и фильтрацией",
     *     tags={"Временные зоны"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы (целое число >= 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         description="Количество элементов на странице (целое число >= 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="fields",
     *         in="query",
     *         description="Список полей через запятую, которые нужно вернуть",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с данными и пагинацией",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные часовых поясов"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Timezone")
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     description="Информация о пагинации",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="last_page", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Пользователь не авторизован",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Пользовать не авторизован и не имеет доступа к данным"),
     *              @OA\Property(property="errors", type="object", nullable=true)
     *          )
     *      ),
     *     @OA\Response(
     *           response=403,
     *           description="Электронный адрес пользователя не подтвержден",
     *           @OA\JsonContent(
     *               @OA\Property(property="status", type="string", example="error"),
     *               @OA\Property(property="message", type="string", example="Электронная почта авторизованного пользователя не подтверждена"),
     *               @OA\Property(property="errors", type="object", nullable=true)
     *           )
     *       ),
     * )
     */
    public function getTimezones(TimezoneFilterRequest $request, PaginatorService $paginator)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $fields = explode(',', $request->get('fields'));
        $data = $this->timezoneRepository->getTimezoneWithPagination($paginator, $fields, $numberCurrentPage, $countOnPage);
        return ApiResponse::success(__('api.timezone_data'),(object)['items'=>TimezoneResource::collection($data['items']), 'pagination' => $data['pagination']]);
    }
}
