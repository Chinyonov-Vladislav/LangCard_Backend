<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\PasswordResetTokenRequest;
use App\Http\Requests\Api\V1\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\Api\V1\AuthRequests\SendResetLinkRequest;
use App\Http\Requests\Api\V1\UpdatePasswordRequests\UpdatePasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\PasswordResetMail;
use App\Repositories\ForgotPasswordRepositories\ForgotPasswordRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    protected ForgotPasswordRepositoryInterface $forgotPasswordRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(ForgotPasswordRepositoryInterface $forgotPasswordRepository, UserRepositoryInterface $userRepository)
    {
        $this->forgotPasswordRepository = $forgotPasswordRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/password/sendResetLink",
     *     summary="Отправка ссылки для сброса пароля",
     *     description="Отправляет письмо со ссылкой для сброса пароля на указанный email",
     *     tags={"Сброс пароля для неавторизованного пользователя"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SendResetLinkRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка успешно отправлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="Ссылка для сброса пароля отправлена."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь с таким email зарегистрирован через OAuth или не существует",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Пользователь не зарегистрирован с паролем."),
     *             @OA\Property(property="errors", type="object", nullable=true,example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Поле email обязательно для заполнения.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function sendResetLink(SendResetLinkRequest $request): JsonResponse
    {
        if (!$this->userRepository->isExistPasswordAccount($request->email))
        {
            return ApiResponse::error(__('api.user_not_registered_with_password'),null, 409);
        }
        $token = Str::uuid();
        $this->forgotPasswordRepository->updateOrCreateTokenByEmail($request->email, $token);
        Mail::to($request->email)->queue(new PasswordResetMail($token));
        return ApiResponse::success(__('api.password_reset_link_sent'));
    }

    /**
     * @OA\Post(
     *     path="/password/reset",
     *     summary="Сброс пароля пользователя",
     *     description="Позволяет сбросить пароль по email и токену сброса",
     *     operationId="resetPassword",
     *     tags={"Сброс пароля для неавторизованного пользователя"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно изменён",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="User password changed successfully"),
     *             @OA\Property(property="data", type="object", nullable=true,example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Неверный токен сброса пароля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid password reset token"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=410,
     *         description="Истёкший токен сброса пароля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Expired password reset token"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибки валидации запроса",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(
     *                      property="password",
     *                      type="array",
     *                      @OA\Items(type="string", example="'Поле \'password\' обязательно для заполнения.'")
     *                  ),
     *                @OA\Property(
     *                       property="password_confirmation",
     *                       type="array",
     *                       @OA\Items(type="string", example="'Поле \'password_confirmation\' обязательно для заполнения.'")
     *                   ),
     *                 @OA\Property(
     *                       property="token",
     *                       type="array",
     *                       @OA\Items(type="string", example="'Поле \'token\' обязательно для заполнения.'")
     *                   ),
     *             )
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $dataResetPasswordToken = $this->forgotPasswordRepository->getInfoAboutTokenResetPassword($request->token);
        if (!$dataResetPasswordToken) {
            return ApiResponse::error(__('api.invalid_password_reset_token'), null, 404);
        }
        if (Carbon::parse($dataResetPasswordToken->created_at)->addMinutes(60)->isPast()) {
            return ApiResponse::error(__('api.expired_password_reset_token'), null, 410);
        }
        $this->forgotPasswordRepository->updatePassword($dataResetPasswordToken->email, $request->password);
        $this->forgotPasswordRepository->deleteToken($request->token);
        return ApiResponse::success(__('api.user_password_changed_successfully'));
    }


    /**
     * @OA\Post  (
     *     path="/password/infoAboutToken",
     *     summary="Получение информации о корректности токена сброса пароля",
     *     description="Позволяет получить информацию о корректности токена сброса пароля",
     *     operationId="infoAboutTokenResetPassword",
     *     tags={"Сброс пароля для неавторизованного пользователя"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PasswordResetTokenRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Информация о корректном токене сброса пароля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="Токен валиден"),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                       property="count_seconds",
     *                       type="integer",
     *                       example=123
     *                   ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Неверный токен сброса пароля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Неверный токен сброса пароля"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=410,
     *         description="Истёкший токен сброса пароля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Срок действия токена истёк"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибки валидации запроса",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="token",
     *                      type="array",
     *                      @OA\Items(type="string", example="The field 'token' is required.")
     *                  ),
     *             )
     *         )
     *     )
     * )
     */
    public function infoAboutToken(PasswordResetTokenRequest $request)
    {
        $dataResetPasswordToken = $this->forgotPasswordRepository->getInfoAboutTokenResetPassword($request->token);
        if (!$dataResetPasswordToken) {
            return ApiResponse::error(__('api.invalid_password_reset_token'), null, 404);
        }
        $timeEndOfToken = Carbon::parse($dataResetPasswordToken->created_at)->addMinutes(60);
        $secondsLeft = Carbon::now()->diffInSeconds($timeEndOfToken);
        if($secondsLeft <= 0)
        {
            return ApiResponse::error("Срок действия токена истёк", null, 410);
        }
        return ApiResponse::success("Токен валиден", (object)["count_seconds"=>(int)$secondsLeft]);
    }


    /**
     * @OA\Post(
     *     path="/updatePassword",
     *     summary="Обновление пароля авторизованного пользователя",
     *     description="Позволяет авторизованному пользователю обновить свой пароль, если он зарегистрирован с email и паролем.",
     *     operationId="updatePassword",
     *     tags={"Обновление пароля авторизованного пользователя"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно обновлён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пароль пользователя успешно изменён"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не зарегистрирован с email и паролем, смена пароля невозможна",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Текущий авторизованный пользователь не зарегистрирован с использованием email - адреса и пароля, поэтому не обладает возможностью сменить пароль!"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized")
     * )
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $authUser = auth()->user();
        if($authUser->email === null)
        {
            return ApiResponse::error('Текущий авторизованный пользователь не обладает электронным адресом, поэтому не имеет возможность сменить пароль!',null, 409);
        }
        $this->forgotPasswordRepository->updatePassword($authUser->email, $request->password);
        return ApiResponse::success(__('api.user_password_changed_successfully'));
    }
}
