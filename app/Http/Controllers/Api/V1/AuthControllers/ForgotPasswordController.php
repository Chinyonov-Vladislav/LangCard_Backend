<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\Api\V1\AuthRequests\SendResetLinkRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\PasswordResetMail;
use App\Repositories\ForgotPasswordRepositories\ForgotPasswordRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
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
    public function sendResetLink(SendResetLinkRequest $request): JsonResponse
    {
        if (!$this->userRepository->isExistPasswordAccount($request->email))
        {
            return ApiResponse::error(__('api.user_not_registered_with_password'),null, 409);
        }
        $token = Str::uuid();
        $this->forgotPasswordRepository->updateOrCreateTokenByEmail($request->email, $token);
        Mail::to($request->email)->send(new PasswordResetMail($request->email, $token));
        return ApiResponse::success(__('api.password_reset_link_sent'));
    }
    public function updatePassword(ResetPasswordRequest $request): JsonResponse
    {
        $dataResetPasswordToken = $this->forgotPasswordRepository->getInfoAboutTokenResetPassword($request->email);

        if (!$dataResetPasswordToken || !Hash::check($request->token, $dataResetPasswordToken->token)) {
            return ApiResponse::error(__('api.invalid_password_reset_token'));
        }
        if (Carbon::parse($dataResetPasswordToken->created_at)->addMinutes(60)->isPast()) {
            return ApiResponse::error(__('api.expired_password_reset_token'));
        }
        $this->forgotPasswordRepository->updatePassword($request->email, $request->password);
        $this->forgotPasswordRepository->deleteTokenByEmail($request->email);
        return ApiResponse::success(__('api.user_password_changed_successfully'));
    }
}
