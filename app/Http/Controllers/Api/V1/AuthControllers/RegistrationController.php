<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Enums\TypeRequestApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\RegistrationRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    protected RegistrationRepositoryInterface $registrationRepository;
    protected UserRepositoryInterface $userRepository;
    protected ApiService $apiService;


    public function __construct(RegistrationRepositoryInterface $registrationRepository, UserRepositoryInterface $userRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->userRepository = $userRepository;
        $this->apiService = app(ApiService::class);
    }
    /**
     * @OA\Post(
     *     path="/registration",
     *     tags={"Регистрация"},
     *     summary="Регистрация нового пользователя",
     *     operationId="registerUser",
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegistrationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно зарегистрирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации входных данных",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Поле email уже используется.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка при регистрации пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Не удалось зарегистрировать пользователя."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function registration(RegistrationRequest $request): JsonResponse
    {
        $this->registrationRepository->registerUser($request->name, $request->email, $request->password, null,null);
        $user = $this->userRepository->getInfoUserAccountByEmail($request->email);
        if($user === null)
        {
            return ApiResponse::error(__('api.user_not_registered'),null,500);
        }
        $timezoneId = $this->apiService->makeRequest($request->ip(),$user->id, TypeRequestApi::timezoneRequest);
        $currencyIdFromDatabase = $this->apiService->makeRequest($request->ip(),$user->id, TypeRequestApi::currencyRequest);
        $this->userRepository->updateTimezoneId($user, $timezoneId);
        $this->userRepository->updateCurrencyId($user, $currencyIdFromDatabase);
        return ApiResponse::success(__('api.user_registered_successfully'), null, 201);
    }
}
