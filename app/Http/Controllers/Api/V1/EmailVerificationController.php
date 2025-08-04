<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EmailVerificationRequest\EmailVerificationCodeRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\EmailVerificationCode;
use App\Mail\InviteCodeMail;
use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepositoryInterface;
use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\GenerationCodeServices\GenerationCodeVerificationEmail;
use App\Services\GenerationCodeServices\GenerationInviteCodeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Random\RandomException;

class EmailVerificationController extends Controller
{
    protected EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository;
    protected InviteCodeRepositoryInterface $inviteCodeRepository;

    protected UserRepositoryInterface $userRepository;

    protected GenerationCodeVerificationEmail $generationCodeEmail;

    protected GenerationInviteCodeService $generationInviteCodeService;



    public function __construct(EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository,
                                InviteCodeRepositoryInterface $inviteCodeRepository,
                                UserRepositoryInterface $userRepository)
    {
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
        $this->inviteCodeRepository = $inviteCodeRepository;
        $this->userRepository = $userRepository;
        $this->generationCodeEmail = new GenerationCodeVerificationEmail($this->emailVerificationCodeRepository);
        $this->generationInviteCodeService = new GenerationInviteCodeService();
    }

    public function sendVerificationCodeEmail()
    {
        try {
            $authUserId = auth()->user()->id;
            $infoCode = $this->emailVerificationCodeRepository->getInfoCodeByUserId($authUserId);
            if($infoCode->email_verified_at !== null)
            {
                return ApiResponse::error('Email - адрес текущего авторизованного пользователя уже был ранее подтвержден!',null, 409);
            }
            $code = $this->generationCodeEmail->generateCode();
            $countMinutes = (int)config('app.expiration_verification_email_code');
            $datetimeExpiration = Carbon::now()->addMinutes($countMinutes);
            $this->emailVerificationCodeRepository->saveVerificationCode($code, $datetimeExpiration, auth()->user()->id);
            Mail::to(auth()->user()->email)->send(new EmailVerificationCode(auth()->user()->email, $code, $countMinutes));
            return ApiResponse::success('Сообщение с кодом для подтверждения email - адреса было отправлено на электронный адрес, указанный при регистрации');
        }
        catch (RandomException $exception)
        {
            logger('Произошла ошибка при генерации кода:'.$exception->getMessage());
            return ApiResponse::error('Произошла ошибка при генерации кода', null, 500);
        }

    }

    public function verificationEmailAddress(EmailVerificationCodeRequest $request)
    {
        $authUserId = auth()->user()->id;
        $infoCode = $this->emailVerificationCodeRepository->getInfoCodeByUserId($authUserId);
        if($infoCode === null)
        {
            return ApiResponse::error("Пользователь с id = $authUserId не найден", null, 404);
        }
        if($infoCode->email_verified_at !== null)
        {
            return ApiResponse::error('Email - адрес текущего авторизованного пользователя уже был ранее подтвержден!',null, 409);
        }
        if($infoCode->email_verification_code === null)
        {
            return ApiResponse::error('Текущий авторизованный пользователь не запрашивал код для подтверждения почты');
        }
        if($infoCode->email_verification_code !== $request->code)
        {
            return ApiResponse::error('Предоставленный код не соответствует коду из электронного сообщения', null, 422);
        }
        $this->emailVerificationCodeRepository->verificateEmailAddress($authUserId);
        if(!$this->userRepository->hasUserInviteCode(auth()->user()->id))
        {
            $code = $this->generationInviteCodeService->generateInviteCode();
            $this->inviteCodeRepository->saveInviteCode(auth()->user()->id, $code);
            Mail::to(auth()->user()->email)->send(new InviteCodeMail(auth()->user()->email, $code));
        }
        return ApiResponse::success('Электронный адрес авторизованного пользователя был успешно подтвержден');
    }
}
