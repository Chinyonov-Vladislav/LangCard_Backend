<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeRequestApi;
use App\Enums\TypeStatusRequestApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateCurrencyRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateLanguageRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateProfileRequest;
use App\Http\Requests\Api\V1\ProfileRequests\UpdateTimezoneRequest;
use App\Http\Resources\V1\CurrencyResources\CurrencyResource;
use App\Http\Resources\V1\LanguageResources\LanguageResource;
use App\Http\Resources\v1\ProfileUserResources\ProfileUserResource;
use App\Http\Resources\V1\TimezoneResources\TimezoneResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected JobStatusRepositoryInterface $jobStatusRepository;

    protected ApiService $apiService;

    private int $countMonthUpdate;

    public function __construct(UserRepositoryInterface $userRepository, JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->userRepository = $userRepository;
        $this->jobStatusRepository = $jobStatusRepository;
        $this->apiService = app(ApiService::class);
        $this->countMonthUpdate = config("app.limit_count_months_to_update_profile_data");
    }


    public function getProfile(int $id)
    {
        $dataUser = $this->userRepository->getInfoUserById($id);
        if ($dataUser === null) {
            return ApiResponse::error("Пользователь с id = $id не найден", null, 404);
        }
        if(auth()->id() === $id)
        {
            $dataUser->jobForDefiningAllParameters = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
            $dataUser->jobForDefiningTimezone = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::timezoneRequest);
            $dataUser->jobForDefiningCurrency = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::currencyRequest);
            $dataUser->jobForDefiningCoordinates = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::coordinatesRequest);
            $dataUser->jobForDefiningLanguage = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::languageRequest);
        }
        return ApiResponse::success("Данные о профиле пользователя с id = $id", (object)['item' => new ProfileUserResource($dataUser)]);
    }


    public function getProfileAuthUser()
    {
        $dataUser = $this->userRepository->getInfoUserById(auth()->user()->id);
        $dataUser->jobForDefiningAllParameters = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::allRequests);
        $dataUser->jobForDefiningTimezone = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::timezoneRequest);
        $dataUser->jobForDefiningCurrency = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::currencyRequest);
        $dataUser->jobForDefiningCoordinates = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::coordinatesRequest);
        $dataUser->jobForDefiningLanguage = $this->jobStatusRepository->getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(auth()->user()->id, TypeRequestApi::languageRequest);
        return ApiResponse::success("Данные о профиле авторизованного пользователя", (object)['item' => new ProfileUserResource($dataUser)]);
    }

    public function updateTimezone(UpdateTimezoneRequest $request)
    {
        if (auth()->user()->last_time_update_timezone !== null && auth()->user()->timezone_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_timezone);
            if ($lastTimeUpdate->addMonths($this->countMonthUpdate)->isFuture()) {
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
            $resultDataFromApi = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::timezoneRequest);
            if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->value) {
                return ApiResponse::success("Задача на автоматическое обновление часового пояса пользователя создана", (object)["job_id" => $resultDataFromApi["job_id"]]);
            }
            if($resultDataFromApi['status'] === TypeStatusRequestApi::error->value)
            {
                return ApiResponse::error("Произошла ошибка при обновлении часового пояса пользователя", (object)["message"=>$resultDataFromApi['message']]);
            }
            $timezone = $this->userRepository->getInfoUserById(auth()->id())->timezone;
            return ApiResponse::success("Данные о часовом поясе пользователя обновлены", (object)["timezone"=>$timezone]);
        }
        $this->userRepository->updateTimezoneIdByIdUser(auth()->user()->id, $request->timezone_id);
        $timezone = $this->userRepository->getInfoUserById(auth()->id())->timezone;
        return ApiResponse::success("Данные о часовом поясе пользователя успешно обновлены", (object)["timezone"=>$timezone]);
    }

    public function updateCurrency(UpdateCurrencyRequest $request)
    {
        if (auth()->user()->last_time_update_currency !== null && auth()->user()->currency_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_currency);
            if ($lastTimeUpdate->addMonths($this->countMonthUpdate)->isFuture()) {
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
            $resultDataFromApi = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::currencyRequest);
            if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->value) {
                return ApiResponse::success("Задача на автоматическое обновление валюты пользователя создана", (object)["job_id" => $resultDataFromApi["job_id"]]);
            }
            if($resultDataFromApi['status'] === TypeStatusRequestApi::error->value)
            {
                return ApiResponse::error("Произошла ошибка при обновлении валюты пользователя", (object)["message"=>$resultDataFromApi['message']]);
            }
            $currency = $this->userRepository->getInfoUserById(auth()->id())->currency;
            return ApiResponse::success("Данные о валюте пользователя успешно обновлены", (object)["currency"=>$currency]);
        }
        $this->userRepository->updateCurrencyIdByIdUser(auth()->user()->id, $request->currency_id);
        $currency = $this->userRepository->getInfoUserById(auth()->id())->currency;
        return ApiResponse::success("Данные о валюте пользователя успешно обновлены", (object)["currency"=>$currency]);
    }

    public function updateLanguage(UpdateLanguageRequest $request)
    {
        if (auth()->user()->last_time_update_language !== null && auth()->user()->language_id !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_language);
            if ($lastTimeUpdate->addMonths($this->countMonthUpdate)->isFuture()) {
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
            $resultDataFromApi = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::languageRequest);
            if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->value) {
                return ApiResponse::success("Задача на автоматическое обновление языка пользователя создана", (object)["job_id" => $resultDataFromApi["job_id"]]);
            }
            if($resultDataFromApi['status'] === TypeStatusRequestApi::error->value)
            {
                return ApiResponse::error("Произошла ошибка при обновлении языка пользователя", (object)["message"=>$resultDataFromApi['message']]);
            }
            $language = $this->userRepository->getInfoUserById(auth()->id())->language;
            return ApiResponse::success("Данные о языка пользователя обновлены", (object)["language"=>$language]);
        }
        $this->userRepository->updateLanguageIdByIdUser(auth()->user()->id, $request->language_id);
        $language = $this->userRepository->getInfoUserById(auth()->id())->language;
        return ApiResponse::success("Данные о языка пользователя обновлены", (object)["language"=>$language]);
    }

    public function updateCoordinates(Request $request)
    {
        if (auth()->user()->last_time_update_coordinates !== null && auth()->user()->latitude !== null && auth()->user()->longitude !== null) {
            $lastTimeUpdate = Carbon::parse(auth()->user()->last_time_update_coordinates);
            if ($lastTimeUpdate->addMonths($this->countMonthUpdate)->isFuture()) {
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
        $resultDataFromApi = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::coordinatesRequest);
        if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->value) {
            return ApiResponse::success("Задача на автоматическое обновление координат пользователя создана", (object)["job_id" => $resultDataFromApi["job_id"]]);
        }
        if($resultDataFromApi['status'] === TypeStatusRequestApi::error->value)
        {
            return ApiResponse::error("Произошла ошибка при обновлении координат пользователя", (object)["message"=>$resultDataFromApi['message']]);
        }
        $infoUser = $this->userRepository->getInfoUserById(auth()->id());
        return ApiResponse::success("Данные о координатах пользователя обновлены", (object)["latitude"=>$infoUser->latitude, "longitude"=>$infoUser->longitude]);
    }

    public function updateFieldsByIp(Request $request)
    {
        $errorsUpdateFields = [];
        $user = auth()->user();

        $fields = [
            "timezone"  => ['last_time_update_timezone', 'timezone_id', "часовом поясе"],
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
                if ($lastTimeUpdate->copy()->addMonths($this->countMonthUpdate)->isFuture()) {
                    $errorsUpdateFields[$key][] = "С последнего момента обновления данных о $fieldForMessage не прошло 6 месяцев. Обновление недоступно";
                }
            }
        }

        $jobs = [
            'allFields'   => TypeRequestApi::allRequests,
            "timezone"  => TypeRequestApi::timezoneRequest,
            'currency'    => TypeRequestApi::currencyRequest,
            'language'    => TypeRequestApi::languageRequest,
            'coordinates' => TypeRequestApi::coordinatesRequest,
        ];

        foreach ($jobs as $key => $typeRequest) {
            if ($this->jobStatusRepository->isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData($user->id, $typeRequest)) {
                $errorsUpdateFields[$key][] = match ($key) {
                    'allFields'   => "Уже существует отложенная задача для автоматического установления всех данных пользователя",
                    'timezone'  => "Уже существует отложенная задача для автоматического установления часового пояса пользователя",
                    'currency'    => "Уже существует отложенная задача для автоматического установления валюты пользователя",
                    'language'    => "Уже существует отложенная задача для автоматического установления языка пользователя",
                    'coordinates' => "Уже существует отложенная задача для автоматического установления координат пользователя",
                };
            }
        }
        if(!empty($errorsUpdateFields)) {
            return ApiResponse::error("Существуют ошибки при попытки обновления всех полей", (object)['errors'=>$errorsUpdateFields], 409);
        }
        $resultDataFromApi = $this->apiService->makeRequest($request->ip(), auth()->user()->id, TypeRequestApi::allRequests);
        if($resultDataFromApi['status'] === TypeStatusRequestApi::delayed->value) {
            return ApiResponse::success("Задача на автоматическое обновление языка, валюты и координат пользователя создана", (object)["job_id" => $resultDataFromApi["job_id"]]);
        }
        if($resultDataFromApi['status'] === TypeStatusRequestApi::error->value)
        {
            return ApiResponse::error("Произошла ошибка при обновлении языка, валюты и координат пользователя", (object)["message"=>$resultDataFromApi['message']]);
        }
        $infoUser = $this->userRepository->getInfoUserById(auth()->id());
        return ApiResponse::success("Полученные данные о языке, валюте и координатах пользователя по ip-адресу успешно обновлены",
            (object)[
                "timezone"=>new TimezoneResource($infoUser->timezone),
                "currency"=>new CurrencyResource($infoUser->currency),
                "language"=>new LanguageResource($infoUser->language),
                "coordinates"=>[
                    'latitude'=>$infoUser->latitude,
                    'longitude'=>$infoUser->longitude,
                ]]);

    }

    public function changeMyVisibility()
    {
        $user = $this->userRepository->changeMyVisibility(auth()->user());
        return ApiResponse::success("Видимость месторасположения успешно изменена", (object)["is_hide_coordinates"=>$user->hideMyCoordinates]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $this->userRepository->updateNameAndAvatar(auth()->id(), $request->name, $request->avatar_url);
        return ApiResponse::success("Данные профиля успешно обновлены");
    }
}
