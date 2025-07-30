<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EmailVerificationRequest\EmailVerificationCodeRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\EmailVerificationCode;
use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepositoryInterface;
use App\Services\GenerationCodeVerificationEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Random\RandomException;

class EmailVerificationController extends Controller
{
    protected EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository;
    protected GenerationCodeVerificationEmail $generationCodeEmail;

    public function __construct(EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository)
    {
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
        $this->generationCodeEmail = new GenerationCodeVerificationEmail($this->emailVerificationCodeRepository);
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
        return ApiResponse::success('Электронный адрес авторизованного пользователя был успешно подтвержден');
    }
}
