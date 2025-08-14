<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
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
     *                  nullable=true,
     *                  @OA\Property(
     *                      property="timezone_job_id",
     *                      type="string",
     *                      description="Идентификатор фоновой задачи на определение часового пояса (если не удалось получить сразу)",
     *                      example="job_12345"
     *                  ),
     *                  @OA\Property(
     *                      property="currency_job_id",
     *                      type="string",
     *                      description="Идентификатор фоновой задачи на определение валюты (если не удалось получить сразу)",
     *                      example="job_67890"
     *                  )
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
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function registration(RegistrationRequest $request): JsonResponse
    {
        $this->registrationRepository->registerUser($request->name, $request->email, $request->password, null, null);
        $user = $this->userRepository->getInfoUserAccountByEmail($request->email);
        if ($user === null) {
            return ApiResponse::error(__('api.user_not_registered'), null, 500);
        }
        $timezoneInfo = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::timezoneRequest);
        $currencyInfo = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::currencyRequest);
        if ($timezoneInfo['status'] === TypeStatus::success->value) {
            $this->userRepository->updateTimezoneId($user, $timezoneInfo['id']);
        }
        if ($currencyInfo['status'] === TypeStatus::success->value) {
            $this->userRepository->updateCurrencyId($user, $currencyInfo['id']);
        }
        $data = null;
        if ($timezoneInfo['status'] === TypeStatus::error->value && $currencyInfo['status'] === TypeStatus::error->value) {
            $data = ["timezone_job_id"=>$timezoneInfo['job_id'], "currency_job_id"=>$currencyInfo['job_id']];
        }
        else if ($timezoneInfo['status'] === TypeStatus::error->value && $currencyInfo['status'] !== TypeStatus::error->value)
        {
            $data = ["timezone_job_id"=>$timezoneInfo['job_id']];
        }
        else if($timezoneInfo['status'] !== TypeStatus::error->value && $currencyInfo['status'] === TypeStatus::error->value)
        {
            $data = ["currency_job_id"=>$currencyInfo['job_id']];
        }
        $this->achievementService->startAchievementsForNewUser($user->id);
        return ApiResponse::success(__('api.user_registered_successfully'), $data === null ? null : (object)$data, 201);
    }
}
