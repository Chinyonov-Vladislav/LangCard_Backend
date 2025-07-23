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
