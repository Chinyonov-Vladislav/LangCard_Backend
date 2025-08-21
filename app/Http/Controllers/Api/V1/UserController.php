<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserRequests\NearByRequest;
use App\Http\Resources\V1\UserResources\NearByUserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserRepositories\UserRepositoryInterface;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function nearBy(NearByRequest $request)
    {
        $user = auth()->user();
        if($user->latitude === null || $user->longitude === null){
            return ApiResponse::error("Невозможно получить пользователей рядом, так как у авторизованного пользователя отсутствуют координаты широты или долготы");
        }
        $users = $this->userRepository->getUsersNearBy($user->latitude,$user->longitude, $request->radius);
        return ApiResponse::success("Получены данные о пользователях, находящихся в радиусе = $request->radius м", (object)["users"=>NearByUserResource::collection($users)]);
    }
}
