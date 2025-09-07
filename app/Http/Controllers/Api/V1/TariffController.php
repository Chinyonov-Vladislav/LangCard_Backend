<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TariffRequests\AddingNewTariffRequest;
use App\Http\Resources\V1\TariffResources\TariffResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;

class TariffController extends Controller
{
    protected TariffRepositoryInterface $tariffRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(TariffRepositoryInterface $tariffRepository, UserRepositoryInterface $userRepository)
    {
        $this->tariffRepository = $tariffRepository;
        $this->userRepository = $userRepository;
    }



    /**
     * @OA\Get(
     *     path="/tariffs",
     *     summary="Получить список активных тарифов",
     *     tags={"Тарифы"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с данными тарифов",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные всех тарифов успешно получены"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 required={"items"},
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/TariffResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function getTariffs()
    {
        $authUserInfo = $this->userRepository->getInfoUserById(auth()->id());
        if($authUserInfo->currency_id === null)
        {
            $data = $this->tariffRepository->getAllActiveTariffs();
            return ApiResponse::success(__('api.all_tariff_data'), (object)['items'=> $data]);
        }
        $data = $this->tariffRepository->getActiveTariffsForUserCurrency($authUserInfo->currency_id);
        return ApiResponse::success(__('api.all_tariff_data_for_user_currency'), (object)['items'=> TariffResource::collection($data)]);
    }

    /**
     * @OA\Post(
     *     path="/tariffs",
     *     summary="Добавить новый тариф",
     *     tags={"Тарифы"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingNewTariffRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тариф успешно создан",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Тариф успешно создан")
     *         )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="name", type="array",
     *                      @OA\Items(type="string", example="Topic name is required.")
     *                  ),
     *                  @OA\Property(property="days", type="array",
     *                       @OA\Items(type="string", example="The 'days' field is required.")
     *                   ),
     *              )
     *          )
     *      ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin"),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера при создании тарифа",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Не удалось создать тариф"),
     *             @OA\Property(property="errors", type="object",nullable=true, example=null),
     *         )
     *     )
     * )
     */
    public function addTariff(AddingNewTariffRequest $request)
    {
        $newTariff = $this->tariffRepository->saveNewTariff($request->name, $request->days);
        if($newTariff === null)
        {
            return ApiResponse::error(__('api.tariff_creation_failed'), null, 500);
        }
        return ApiResponse::success(__('api.tariff_created_successfully'));
    }


    /**
     * @OA\Patch(
     *     path="/tariffs/{id}",
     *     summary="Изменить статус тарифа",
     *     tags={"Тарифы"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID тарифа",
     *         required=true,
     *         example=1,
     *         @OA\Schema(type="integer", format="int64", minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус тарифа успешно изменён",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Статус тарифа с ID 1 изменён")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тариф с указанным ID не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Тариф с ID 1 не найден")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin")
     * )
     */
    public function changeTariffStatus($id)
    {
        if(!$this->tariffRepository->isExistTariffById($id))
        {
            return ApiResponse::error(__('api.tariff_not_found', ['id'=>$id]), null, 404);
        }
        $this->tariffRepository->changeStatus($id);
        return ApiResponse::success(__('api.tariff_status_changed', ['id'=>$id]));
    }
}
