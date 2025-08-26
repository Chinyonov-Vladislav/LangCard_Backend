<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeRequestApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateCurrencyRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateLanguageRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateProfileRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateTimezoneRequest;
use App\Http\Resources\v1\ProfileUserResources\ProfileUserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ProfileController extends Controller
{
    private const COUNT_MONTH_UPDATE = 6;
    protected UserRepositoryInterface $userRepository;
    protected JobStatusRepositoryInterface $jobStatusRepository;

    protected ApiService $apiService;

    public function __construct(UserRepositoryInterface $userRepository, JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->userRepository = $userRepository;
        $this->jobStatusRepository = $jobStatusRepository;
        $this->apiService = app(ApiService::class);
    }


    /**
     * @OA\Get(
     *     path="/profile/{id}",
     *     summary="Получение профиля пользователя",
     *     description="Возвращает данные профиля. Если id не передан — возвращаются данные авторизованного пользователя.",
     *     tags={"Профиль"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID пользователя. Если не указан — берётся ID авторизованного пользователя.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные профиля успешно получены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о профиле пользователя с id = 15"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="item",
     *                     ref="#/components/schemas/ProfileUserResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Пользователь с id = 15 не найден"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function getProfile(int $id)
    {
        $isAuthUser = auth()->user()->id === $id;
        $dataUser = $this->userRepository->getInfoUserById($id);
        if ($dataUser === null) {
            return ApiResponse::error("Пользователь с id = $id не найден", null, 404);
        }
        $dataUser->isAuthUser = $isAuthUser;
        return ApiResponse::success("Данные о профиле пользователя с id = $id", (object)['item' => new ProfileUserResource($dataUser)]);
    }


    /**
     * @OA\Get(
     *     path="/profile/",
     *     summary="Получение профиля авторизованного пользователя",
     *     description="Возвращает данные профиля авторизованного пользователя",
     *     tags={"Профиль"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Данные профиля успешно получены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о профиле пользователя с id = 15"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="item",
     *                     ref="#/components/schemas/ProfileUserResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function getProfileAuthUser()
    {
        $dataUser = $this->userRepository->getInfoUserById(auth()->user()->id);
        $dataUser->isAuthUser = true;
        return ApiResponse::success("Данные о профиле авторизованного пользователя", (object)['item' => new ProfileUserResource($dataUser)]);
    }

    public function updateTimezone(UpdateTimezoneRequest $request)
    {
        if (auth()->user()->last_time_update_timezone !== null && auth()->user()->currency_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_timezone);
            if ($lastTimeUpdate->addMonths(self::COUNT_MONTH_UPDATE)->isFuture()) {
                return ApiResponse::error("С последнего момента обновления данных о часовом поясе не прошло 6 месяцев. Обновление недоступно", null, 409);
            }
        }
        $isExistJobForDefiningAllParameters = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
        if ($isExistJobForDefiningAllParameters) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления всех данных пользователя", null, 409);
        }
        $isExistJob = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::timezoneRequest);
        if ($isExistJob) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления временной зоны пользователя", null, 409);
        }

        if ($request->automatic) {
            $jobId = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::timezoneRequest);
            return ApiResponse::success("Задача на автоматическое обновление часового пояса пользователя создана", (object)["job_id" => $jobId]);
        }
        $this->userRepository->updateTimezoneIdByIdUser(auth()->user()->id, $request->timezone_id);
        return ApiResponse::success("Данные о часовом поясе пользователя успешно обновлены");
    }

    public function updateCurrency(UpdateCurrencyRequest $request)
    {
        if (auth()->user()->last_time_update_currency !== null && auth()->user()->timezone_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_currency);
            if ($lastTimeUpdate->addMonths(self::COUNT_MONTH_UPDATE)->isFuture()) {
                return ApiResponse::error("С последнего момента обновления данных о валюте не прошло 6 месяцев. Обновление недоступно", null, 409);
            }
        }
        $isExistJobForDefiningAllParameters = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
        if ($isExistJobForDefiningAllParameters) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления всех данных пользователя", null, 409);
        }
        $isExistJob = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::currencyRequest);
        if ($isExistJob) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления валюты пользователя", null, 409);
        }
        if ($request->automatic) {
            $jobId = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::currencyRequest);
            return ApiResponse::success("Задача на автоматическое обновление валюты пользователя создана", (object)["job_id" => $jobId]);
        }
        $this->userRepository->updateCurrencyIdByIdUser(auth()->user()->id, $request->currency_id);
        return ApiResponse::success("Данные о валюте пользователя успешно обновлены");
    }

    public function updateLanguage(UpdateLanguageRequest $request)
    {
        if (auth()->user()->last_time_update_language !== null && auth()->user()->language_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_language);
            if ($lastTimeUpdate->addMonths(self::COUNT_MONTH_UPDATE)->isFuture()) {
                return ApiResponse::error("С последнего момента обновления данных о языке не прошло 6 месяцев. Обновление недоступно", null, 409);
            }
        }
        $isExistJobForDefiningAllParameters = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
        if ($isExistJobForDefiningAllParameters) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления всех данных пользователя", null, 409);
        }
        $isExistJob = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::languageRequest);
        if ($isExistJob) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления языка пользователя", null, 409);
        }
        if ($request->automatic) {
            $jobId = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::languageRequest);
            return ApiResponse::success("Задача на автоматическое обновление валюты пользователя создана", (object)["job_id" => $jobId]);
        }
        $this->userRepository->updateLanguageIdByIdUser(auth()->user()->id, $request->currency_id);
        return ApiResponse::success("Данные об языке пользователя успешно обновлены");
    }

    public function updateCoordinates(Request $request)
    {
        if (auth()->user()->last_time_update_coordinates !== null && auth()->user()->latitude !== null && auth()->user()->longitude !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_coordinates);
            if ($lastTimeUpdate->addMonths(self::COUNT_MONTH_UPDATE)->isFuture()) {
                return ApiResponse::error("С последнего момента обновления данных о координатах не прошло 6 месяцев. Обновление недоступно", null, 409);
            }
        }
        $isExistJobForDefiningAllParameters = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
        if ($isExistJobForDefiningAllParameters) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления всех данных пользователя", null, 409);
        }
        $isExistJob = $this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::coordinatesRequest);
        if ($isExistJob) {
            return ApiResponse::error("Уже существует отложенная задача для автоматического установления координат пользователя", null, 409);
        }
        $jobId = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::coordinatesRequest);
        return ApiResponse::success("Задача на автоматическое обновление координат пользователя создана", (object)["job_id" => $jobId]);
    }

    public function updateFieldsByIp(Request $request)
    {
        $errorsUpdateFields = [];
        $user = auth()->user();

        $fields = [
            'timezone'    => ['last_time_update_timezone', 'timezone_id', "часовом поясе"],
            'currency'    => ['last_time_update_currency', 'currency_id', "валюте"],
            'language'    => ['last_time_update_language', 'language_id', "языке"],
            'coordinates' => ['last_time_update_coordinates', ['latitude', 'longitude'], "координатах"],
        ];

        foreach ($fields as $key => [$timeField, $checkField, $fieldForMessage]) {
            $hasValue = is_array($checkField)
                ? $user->{$checkField[0]} !== null && $user->{$checkField[1]} !== null
                : $user->{$checkField} !== null;

            if ($user->{$timeField} !== null && $hasValue) {
                $lastTimeUpdate = Carbon::parse($user->{$timeField});
                if ($lastTimeUpdate->copy()->addMonths(self::COUNT_MONTH_UPDATE)->isFuture()) {
                    $errorsUpdateFields[$key][] = "С последнего момента обновления данных о $fieldForMessage не прошло 6 месяцев. Обновление недоступно";
                }
            }
        }

        $jobs = [
            'allFields'   => TypeRequestApi::allRequests,
            'timezone'    => TypeRequestApi::timezoneRequest,
            'currency'    => TypeRequestApi::currencyRequest,
            'language'    => TypeRequestApi::languageRequest,
            'coordinates' => TypeRequestApi::coordinatesRequest,
        ];

        foreach ($jobs as $key => $typeRequest) {
            if ($this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData($user->id, $typeRequest)) {
                $errorsUpdateFields[$key][] = match ($key) {
                    'allFields'   => "Уже существует отложенная задача для автоматического установления всех данных пользователя",
                    'timezone'    => "Уже существует отложенная задача для автоматического установления временной зоны пользователя",
                    'currency'    => "Уже существует отложенная задача для автоматического установления валюты пользователя",
                    'language'    => "Уже существует отложенная задача для автоматического установления языка пользователя",
                    'coordinates' => "Уже существует отложенная задача для автоматического установления координат пользователя",
                };
            }
        }
        if(!empty($errorsUpdateFields)) {
            return ApiResponse::error("Существуют ошибки при попытки обновления всех полей", (object)['errors'=>$errorsUpdateFields], 409);
        }
        $jobId = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::allRequests);
        return ApiResponse::success("Задача на автоматическое обновление пользовательских данных по ip - адресу создана", (object)["job_id" => $jobId]);
    }

    public function changeMyVisibility()
    {
        $this->userRepository->changeMyVisibility(auth()->user());
        return ApiResponse::success("Видимость месторасположения успешно изменена");
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $this->userRepository->updateNameAndAvatar(auth()->id(), $request->name, $request->avatar_url);
        return ApiResponse::success("Данные профиля успешно обновлены");
    }
}
