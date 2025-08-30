<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Enums\TypeRequestApi;
use App\Enums\TypeStatusRequestApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\RegistrationRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\AchievementService;
use App\Services\ApiServices\ApiService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    protected RegistrationRepositoryInterface $registrationRepository;
    protected UserRepositoryInterface $userRepository;
    protected ApiService $apiService;

    protected AchievementService $achievementService;

    public function __construct(RegistrationRepositoryInterface $registrationRepository, UserRepositoryInterface $userRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->userRepository = $userRepository;
        $this->apiService = app(ApiService::class);
        $this->achievementService = new AchievementService();
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
     *     @OA\Response(
     *          response=201,
     *          description="Пользователь успешно зарегистрирован",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  nullable = true,
     *                  @OA\Property(
     *                       property="job_id",
     *                       type="string",
     *                       description="Идентификатор фоновой задачи на определение валюты, часового пояса и языка пользователя",
     *                       example="job_67890"
     *                   )
     *              )
     *          )
     *      ),
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
     *                 ),
     *                 @OA\Property(
     *                      property="name",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле name обязательно для заполнения.")
     *                  ),
     *                @OA\Property(
     *                       property="password",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле password обязательно для заполнения.")
     *                   ),
     *                @OA\Property(
     *                        property="mailing_enabled",
     *                        type="array",
     *                        @OA\Items(type="string", example="Поле mailing_enabled обязательно для заполнения.")
     *                    ),
     *             )
     *         )
     *     ),
     * )
     */
    public function registration(RegistrationRequest $request): JsonResponse
    {
        $user = $this->registrationRepository->registerUser($request->name, $request->email, $request->password, null, mailing_enabled: $request->mailing_enabled);
        $resultDataFromApi = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::allRequests);
        $this->achievementService->startAchievementsForNewUser($user->id);
        if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->name) {
            return ApiResponse::success(__('api.user_registered_successfully'), (object)["job_id" => $resultDataFromApi["job_id"]], 201);
        }
        return ApiResponse::success(__('api.user_registered_successfully'), null, 201);
    }
}
