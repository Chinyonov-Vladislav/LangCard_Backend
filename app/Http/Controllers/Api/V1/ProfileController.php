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
        if($dataUser === null){
            return ApiResponse::error("Пользователь с id = $id не найден", null, 404);
        }
        $dataUser->isAuthUser = $isAuthUser;
        return ApiResponse::success("Данные о профиле пользователя с id = $id", (object)['item'=>new ProfileUserResource($dataUser)]);
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
        return ApiResponse::success("Данные о профиле авторизованного пользователя", (object)['item'=>new ProfileUserResource($dataUser)]);
    }
}
