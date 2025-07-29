<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ProfileUserResources\ProfileUserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Auth;

class ProfileController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getProfile(?int $id = null)
    {
        if($id === null){
            $id = Auth::id();
        }
        $dataUser = $this->userRepository->getInfoUserById($id);
        if($dataUser === null){
            return ApiResponse::error("Пользователь с id = $id не найден", null, 404);
        }
        return ApiResponse::success("Данные о профиле пользователя с id = $id", (object)['item'=>new ProfileUserResource($dataUser)]);
    }
}
