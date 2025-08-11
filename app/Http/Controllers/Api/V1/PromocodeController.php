<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypePdfPromocodes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PromocodeRequests\ActivatePromocodeRequest;
use App\Http\Requests\Api\V1\PromocodeRequests\CreatePromocodeRequest;
use App\Http\Resources\V1\ProfileUserResources\VipStatusEndResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\PromocodeRepositories\PromocodeRepositoryInterface;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\GenerationCodeServices\PromocodeGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class PromocodeController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected TariffRepositoryInterface $tariffRepository;
    protected PromocodeRepositoryInterface $promocodeRepository;
    protected PromocodeGeneratorService $promocodeGeneratorService;

    public function __construct(PromocodeGeneratorService    $promocodeGeneratorService,
                                PromocodeRepositoryInterface $promocodeRepository,
                                TariffRepositoryInterface    $tariffRepository,
                                UserRepositoryInterface      $userRepository)
    {
        $this->promocodeGeneratorService = $promocodeGeneratorService;
        $this->promocodeRepository = $promocodeRepository;
        $this->tariffRepository = $tariffRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/promocodes",
     *     operationId="createPromocodes",
     *     tags={"Промокоды"},
     *     summary="Создание нескольких промокодов",
     *     description="Создаёт указанное количество промокодов и автоматически привязывает их к случайным активным тарифам. Доступно только администраторам.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePromocodeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Промокоды успешно созданы",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Промокоды были успешно созданы в количестве 10 штук"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Нет существующих тарифов для привязки промокодов",
     *         @OA\JsonContent(
     *              type="object",
     *              required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Нет существующих тарифов"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *             response=422,
     *             description="Ошибка валидации",
     *             @OA\JsonContent(
     *                  type="object",
     *                  required = {"message", "errors"},
     *                 @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                 @OA\Property(property="errors", type="object",
     *                     @OA\Property(property="count", type="array",
     *                         @OA\Items(type="string", example="Поле 'count' обязательно для заполнения")
     *                     ),
     *                 )
     *             )
     *         ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Сообщение об ошибке"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function createPromocodes(CreatePromocodeRequest $request)
    {
        try {
            $allIdActiveTariffs = $this->tariffRepository->getAllIdActiveTariffs();
            if (count($allIdActiveTariffs) === 0) {
                return ApiResponse::error('Нет существующих тарифов', null, 404);
            }
            $codes = $this->promocodeGeneratorService->generateCertainCountCode($request->count);
            foreach ($codes as $code) {
                $this->promocodeRepository->saveNewPromocode($code, $allIdActiveTariffs[array_rand($allIdActiveTariffs)]);
            }
            return ApiResponse::success("Промокоды были успешно созданы в количестве $request->count штук");
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/promocodes/activate",
     *     operationId="activatePromocode",
     *     tags={"Промокоды"},
     *     summary="Активация промокода пользователем",
     *     description="Позволяет пользователю активировать промокод для продления VIP-статуса. Требует авторизации через Bearer токен.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ActivatePromocodeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Промокод успешно активирован, VIP-статус обновлен",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Обновлена дата окончания VIP - статуса."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="new_date_end_vip_status",
     *                     ref="#/components/schemas/VipStatusEndResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Промокод не найден",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Промокод не найден"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Промокод уже был активирован ранее",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Предоставленный промокод уже был активирован ранее"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              type = "object",
     *              required = {"message","errors"},
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object",
     *                      @OA\Property(property="code", type="array",
     *                          @OA\Items(type="string", example="Поле 'code' обязательно для заполнения")
     *                      ),
     *                  )
     *              )
     *          ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function activatePromocode(ActivatePromocodeRequest $request)
    {
        $promocode = $this->promocodeRepository->getPromocodeByCode($request->code);
        if ($promocode === null) {
            return ApiResponse::error('Промокод не найден', null, 404);
        }
        if ($promocode->active === false) {
            return ApiResponse::error('Предоставленный промокод уже был активирован ранее', null, 409);
        }
        $user = auth()->user();
        $currentEndDate = $user->vip_status_time_end ? Carbon::parse($user->vip_status_time_end) : Carbon::now();
        $dateEndOfVipStatus = $currentEndDate->max(Carbon::now())->addDays($promocode->tariff->days);
        $this->userRepository->updateEndDateOfVipStatusByIdUser($user->id, $dateEndOfVipStatus);
        $this->promocodeRepository->deactivatePromocodeByPromocode($promocode);
        $userInfo = $this->userRepository->getInfoUserById($user->id);
        return ApiResponse::success("Обновлена дата окончания VIP - статуса.",
            (object)['new_date_end_vip_status' => new VipStatusEndResource($userInfo)]);
    }

    /**
     * @OA\Get(
     *     path="/promocodes/download/{type}/{tariffId}",
     *     operationId="downloadPromocodesCertaionTariff",
     *     tags={"Промокоды"},
     *     summary="Скачать PDF с активными промокодами для конкретного тарифа",
     *     description="Возвращает PDF-файл с активными промокодами для конкретного тарифа",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Тип PDF: 'table' — таблица, 'card' — карточки",
     *         @OA\Schema(
     *             type="string",
     *             enum={"table", "card"},
     *             example="table"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tariffId",
     *         in="path",
     *         required=true,
     *         description="Идентификатор тарифа",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PDF-файл с активными промокодами",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тариф с указанным id не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status", "message", "errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Тариф с id = 1 не существует"),
     *             @OA\Property(property="errors", type="object", nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function downloadPromocodesCertainTariff(string $type, int $tariffId)
    {
        if (!$this->tariffRepository->isExistTariffById($tariffId)) {
            return ApiResponse::error("Тариф с id = $tariffId не существует", null, 404);
        }
        $promocodes = $this->promocodeRepository->getActivePromocodesById($tariffId);
        $pdf = PDF::loadView(match ($type) {
            TypePdfPromocodes::table->value => 'pdf.promocodes',
            default => 'pdf.promocodes_cards'
        }, ['promocodes' => $promocodes]);
        return $pdf->download('promo-codes.pdf');
    }

    /**
     * @OA\Get(
     *     path="/promocodes/download/{type}",
     *     operationId="downloadPromocodesAllTariffs",
     *     tags={"Промокоды"},
     *     summary="Скачать PDF с активными промокодами для всех тарифов",
     *     description="Возвращает PDF-файл с активными промокодами для всех тарифов",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Тип PDF: 'table' — таблица, 'card' — карточки",
     *         @OA\Schema(
     *             type="string",
     *             enum={"table", "card"},
     *             example="table"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF-файл с активными промокодами",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     * )
     */
    public function downloadPromocodesAllTariffs(string $type)
    {
        $promocodes = $this->promocodeRepository->getActivePromocodesById(null);
        $pdf = PDF::loadView(match ($type) {
            TypePdfPromocodes::table->value => 'pdf.promocodes',
            default => 'pdf.promocodes_cards'
        }, ['promocodes' => $promocodes]);
        return $pdf->download('promo-codes.pdf');
    }
}
