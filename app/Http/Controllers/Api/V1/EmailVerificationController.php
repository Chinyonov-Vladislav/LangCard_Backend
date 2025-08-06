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


    /**
     * @OA\Post(
     *     path="/sendVerificationCodeEmail",
     *     summary="Отправка кода подтверждения на email",
     *     description="Отправляет код подтверждения на email текущего авторизованного пользователя, если его email ещё не был подтверждён.",
     *     operationId="sendVerificationCodeEmail",
     *     tags={"Верификация электронного адреса"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Код подтверждения успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Сообщение с кодом для подтверждения email - адреса было отправлено на электронный адрес, указанный при регистрации"
     *             ),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Email уже подтверждён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email - адрес текущего авторизованного пользователя уже был ранее подтвержден!"
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка при генерации кода",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Произошла ошибка при генерации кода"
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     )
     * )
     */
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
            Mail::to(auth()->user()->email)->queue(new EmailVerificationCode(auth()->user()->email, $code, $countMinutes));
            return ApiResponse::success('Сообщение с кодом для подтверждения email - адреса было отправлено на электронный адрес, указанный при регистрации');
        }
        catch (RandomException $exception)
        {
            logger('Произошла ошибка при генерации кода:'.$exception->getMessage());
            return ApiResponse::error('Произошла ошибка при генерации кода', null, 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/verificationEmailAddress",
     *     summary="Подтверждение email авторизованного пользователя кодом",
     *     description="Позволяет подтвердить email пользователя по коду, отправленному на электронную почту.",
     *     operationId="verificationEmailAddress",
     *     tags={"Верификация электронного адреса"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EmailVerificationCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email успешно подтверждён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Электронный адрес авторизованного пользователя был успешно подтвержден"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Пользователь с id = 5 не найден"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Email уже подтверждён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email - адрес текущего авторизованного пользователя уже был ранее подтвержден!"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Код не был запрошен ранее",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Текущий авторизованный пользователь не запрашивал код для подтверждения почты"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=412,
     *         description="Неверный код подтверждения",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Предоставленный код не соответствует коду из электронного сообщения"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Предоставленные данные не валидны"),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="code",
     *                      type="array",
     *                      @OA\Items(type="string", example="Token is required")
     *                  ),
     *              )
     *          )
     *      )
     * )
     */
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
            return ApiResponse::error('Предоставленный код не соответствует коду из электронного сообщения', null, 412);
        }
        $this->emailVerificationCodeRepository->verificateEmailAddress($authUserId);
        if(!$this->userRepository->hasUserInviteCode(auth()->user()->id))
        {
            $code = $this->generationInviteCodeService->generateInviteCode();
            $this->inviteCodeRepository->saveInviteCode(auth()->user()->id, $code);
            Mail::to(auth()->user()->email)->queue(new InviteCodeMail(auth()->user()->email, $code));
        }
        return ApiResponse::success('Электронный адрес авторизованного пользователя был успешно подтвержден');
    }
}
