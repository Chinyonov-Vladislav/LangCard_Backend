<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeTwoFactorAuthorization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\CodeGoogleAuthenticatorRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\ConfirmationCodeEmailTwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\EnableDisableATwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\TwoFactorAuthorizationTokenRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\UseRecoveryCodeRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\EmailTwoFactorAuthorizationMail;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\RecoveryCodeRepositories\RecoveryCodeRepositoryInterface;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use App\Services\CookieService;
use App\Services\GenerationCodeServices\GenerationAuthTokenService;
use App\Services\GenerationCodeServices\GenerationRecoveryCodeService;
use App\Services\GenerationCodeServices\GenerationTwoFactorAuthorizationToken;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use QrCode;

class TwoFactorAuthorizationController extends Controller
{
    protected TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository;
    protected LoginRepositoryInterface $loginRepository;

    protected RecoveryCodeRepositoryInterface $recoveryCodeRepository;
    protected GenerationAuthTokenService $generationAuthTokenService;
    protected CookieService $cookieService;

    protected GenerationTwoFactorAuthorizationToken $generationTwoFactorAuthorizationToken;

    protected GenerationRecoveryCodeService $generationRecoveryCodeService;

    public function __construct(TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository,
                                LoginRepositoryInterface $loginRepository,
                                RecoveryCodeRepositoryInterface $recoveryCodeRepository)
    {
        $this->twoFactorAuthorizationRepository = $twoFactorAuthorizationRepository;
        $this->loginRepository = $loginRepository;
        $this->recoveryCodeRepository = $recoveryCodeRepository;
        $this->generationAuthTokenService = new GenerationAuthTokenService();
        $this->cookieService = new CookieService();
        $this->generationTwoFactorAuthorizationToken = new GenerationTwoFactorAuthorizationToken();
        $this->generationRecoveryCodeService = new GenerationRecoveryCodeService();
    }


    /**
     * @OA\Post(
     *     path="/twoFactorVerification",
     *     summary="Включение или отключение двухфакторной аутентификации",
     *     description="Включает или отключает двухфакторную аутентификацию (email или Google Authenticator) для текущего пользователя",
     *     operationId="enableDisableTwoFactorAuthorization",
     *     tags={"Двухфакторная аутентификация"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EnableDisableATwoFactorAuthorizationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Двухфакторная авторизация успешно включена или отключена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Двухфакторная авторизация через Google Authenticator подключена"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="email2fa_enable", type="boolean", example=true),
     *                 @OA\Property(property="google2fa_enable", type="boolean", example=true),
     *                 @OA\Property(property="secret", type="string", example="JBSWY3DPEHPK3PXP", nullable=true),
     *                 @OA\Property(property="qr", type="string", format="byte", description="QR-код в base64", nullable=true),
     *                 @OA\Property(
     *                     property="recovery_codes",
     *                     type="array",
     *                     @OA\Items(type="string", example="8XF2-HZ91"),
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Неподдерживаемый тип двухфакторной авторизации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Неподдерживаемый тип двухфакторной авторизации"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Невозможно включить двухфакторную аутентификацию по email (email отсутствует)",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Вы не можете включить двухфакторную авторизацию через электронную почту, так как аккаунт не имеет электронной почты"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка генерации Google QR-кода или ключа",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Произошла ошибка при подключении двухфакторной авторизации через Google Authenticator"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized")
     * )
     */
    public function enableDisableTwoFactorAuthorization(EnableDisableATwoFactorAuthorizationRequest $request)
    {
        $authUser = auth()->user();
        switch ($request->type) {
            case TypeTwoFactorAuthorization::email->value:
                if($authUser->email === null)
                {
                    return ApiResponse::error('Вы не можете включить двухфакторную авторизацию через электронную почту, так как аккаунт не имеет электронной почты', null, 409);
                }
                $authUser = $this->twoFactorAuthorizationRepository->switchTwoFactorAuthenticationEmail($authUser->id);
                if(!$authUser->two_factor_email_enabled) {
                    if(!$authUser->google2fa_enable) {
                        $this->recoveryCodeRepository->deleteRecoveryCodesForUser($authUser->id);
                    }
                    return ApiResponse::success('Двухфакторная авторизация через электронную почту отключена', (object)['email2fa_enable' => $authUser->two_factor_email_enabled, 'google2fa_enable'=>$authUser->google2fa_enable]);
                }
                $recoveryCodes = $this->createRecoveryCodesForUser($authUser->id);
                return ApiResponse::success('Двухфакторная авторизация через электронную почту подключена', (object)['email2fa_enable' => $authUser->two_factor_email_enabled,'google2fa_enable'=>$authUser->google2fa_enable, 'recovery_codes'=>$recoveryCodes]);
            case TypeTwoFactorAuthorization::googleAuthenticator->value:
                $authUser = $this->twoFactorAuthorizationRepository->switchTwoFactorGoogleAuthenticator($authUser->id);
                if($authUser->google2fa_enable === false) {
                    if(!$authUser->two_factor_email_enabled) {
                        $this->recoveryCodeRepository->deleteRecoveryCodesForUser($authUser->id);
                    }
                    return ApiResponse::success('Двухфакторная авторизация через Google Authenticator была отключена', (object)['email2fa_enable' => $authUser->two_factor_email_enabled, 'google2fa_enable'=>$authUser->google2fa_enable]);
                }
                else
                {
                    $google2fa = app('pragmarx.google2fa');
                    try {
                        $secret = $google2fa->generateSecretKey();
                    }
                    catch (Exception $exception)
                    {
                        logger("Ошибка при подключении двухфакторной авторизации через Google Authenticator: ".$exception->getMessage());
                        return ApiResponse::success("Произошла ошибка при подключении двухфакторной авторизации через Google Authenticator", null,500);
                    }
                    $encryptedSecret = Crypt::encrypt($secret);
                    $authUser = $this->twoFactorAuthorizationRepository->setSecretKeyForTwoFactorAuthenticationGoogle($authUser->id, $encryptedSecret);
                    $qrUrl = $google2fa->getQRCodeUrl(
                        config('app.name'),
                        $authUser->name,
                        $secret
                    );
                    $qr = QrCode::format('svg')->size(200)->generate($qrUrl);
                    $recoveryCodes = $this->createRecoveryCodesForUser($authUser->id);
                    return ApiResponse::success('Двухфакторная авторизация через Google Authenticator подключена. Отсканируйте код в мобильном приложении аутентификатора',
                        (object)[
                            'email2fa_enable' => $authUser->two_factor_email_enabled,
                            'google2fa_enable'=>$authUser->google2fa_enable,
                            'secret'=>$secret,
                            'qr'=>base64_encode($qr),
                            'recovery_codes'=>$recoveryCodes]);
                }
            default:
                return ApiResponse::error('Неподдерживаемый тип двухфакторной авторизации');
        }
    }

    /**
     * @OA\Post(
     *     path="/twoFactorVerification/sendEmailWithCode",
     *     summary="Отправка email с кодом двухфакторной аутентификации",
     *     description="Отправляет код подтверждения на email пользователя, если включена двухфакторная авторизация через email.",
     *     operationId="sendEmailWithCode",
     *     tags={"Двухфакторная аутентификация"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TwoFactorAuthorizationTokenRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Код успешно отправлен на email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Сообщение с кодом было отправлено на электронную почту"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="У пользователя отключена двухфакторная авторизация по email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Для пользователя с email - адресом выключена двухфакторная авторизация"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Токен не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Отсутствует запись о токене двухфакторной авторизации"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации токена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"token": {"Token is required"}}
     *             )
     *         )
     *     )
     * )
     */
    public function sendEmailWithCode(TwoFactorAuthorizationTokenRequest $request)
    {
        $hashedToken = $this->generationTwoFactorAuthorizationToken->hashToken($request->token);
        $tokenInfo = $this->twoFactorAuthorizationRepository->getTokenWithUser($hashedToken);
        if($tokenInfo === null)
        {
            return ApiResponse::error('Отсутствует запись о токене двухфакторной авторизации', null, 404);
        }
        if($tokenInfo->user->two_factor_email_enabled === false)
        {
            return ApiResponse::success('Для пользователя с email - адресом выключена двухфакторная авторизация',null, 409 );
        }
        $countMinutes = (int)config('app.expiration_verification_email_code');
        $code = implode('', array_map(fn () => rand(0, 9), range(1, 6)));
        $expirationTime = Carbon::now()->addMinutes($countMinutes);
        $this->twoFactorAuthorizationRepository->updateDataTwoFactorAuthenticationEmail($tokenInfo->user->id, $code, $expirationTime);
        Mail::to($tokenInfo->user->email)->queue(new EmailTwoFactorAuthorizationMail($code, $countMinutes));
        return ApiResponse::success('Сообщение с кодом было отправлено на электронную почту');
    }

    /**
     * @OA\Post(
     *     path="/twoFactorVerification/confirmEmailCode",
     *     summary="Подтверждение кода двухфакторной аутентификации по Email",
     *     description="Подтверждение кода, отправленного по Email для двухфакторной аутентификации.",
     *     operationId="confirmCode",
     *     tags={"Двухфакторная аутентификация"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ConfirmationCodeEmailTwoFactorAuthorizationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно прошёл двухфакторную авторизацию",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно прошёл двухфакторную авторизацию"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         ),
     *         @OA\Header(header="Set-Cookie", description="Refresh token cookie", @OA\Schema(type="string"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Введенный код некорректен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Введенный код некорректен! Повторите попытку ввода!"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Отсутствует запись о токене двухфакторной авторизации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Отсутствует запись о токене двухфакторной авторизации"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Двухфакторная авторизация отключена или код не запрашивался",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message",
     *                          type="string",
     *                          example="Для текущего пользователя отключена двухфакторная авторизация | Текущий пользователь не запрашивал код для двухфакторной авторизации по электронной почте"
     *                        ),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="Срок действия кода истёк",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Срок действия кода истёк. Запросите новый код"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function confirmCode(ConfirmationCodeEmailTwoFactorAuthorizationRequest $request)
    {
        $hashedToken = $this->generationTwoFactorAuthorizationToken->hashToken($request->token);
        $tokenInfo = $this->twoFactorAuthorizationRepository->getTokenWithUser($hashedToken);
        if($tokenInfo === null)
        {
            return ApiResponse::error('Отсутствует запись о токене двухфакторной авторизации', null, 404);
        }
        if($tokenInfo->user->two_factor_email_enabled === false)
        {
            return ApiResponse::error('Для текущего пользователя отключена двухфакторная авторизация',null, 409);
        }
        if($tokenInfo->user->two_factor_code_email === null || $tokenInfo->user->two_factor_code_email_expiration_date === null)
        {
            return ApiResponse::error('Текущий пользователь не запрашивал код для двухфакторной авторизации по электронной почте',null, 409);
        }
        $expirationDate = Carbon::parse($tokenInfo->user->two_factor_code_email_expiration_date);
        if($expirationDate->isPast())
        {
            return ApiResponse::error('Срок действия кода истёк. Запросите новый код',null, 410);
        }
        if($request->code !== $tokenInfo->user->two_factor_code_email)
        {
            return ApiResponse::error('Введенный код некорректен! Повторите попытку ввода!', null, 400);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

    /**
     * @OA\Post(
     *     path="/twoFactorVerification/verifyCodeGoogle2fa",
     *     summary="Подтверждение Google Authenticator кода",
     *     description="Подтверждает код из приложения Google Authenticator. При успешной проверке возвращает access token и устанавливает refresh token в cookie.",
     *     operationId="verifyCodeGoogle2fa",
     *     tags={"Двухфакторная аутентификация"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CodeGoogleAuthenticatorRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно прошёл двухфакторную авторизацию",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно прошёл двухфакторную авторизацию"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJhbGciOi...")
     *             )
     *         ),
     *         headers={
     *             @OA\Header(
     *                 header="Set-Cookie",
     *                 description="Устанавливает refresh_token в cookie",
     *                 @OA\Schema(type="string", example="refresh_token=...; HttpOnly; Path=/; Max-Age=3600")
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="У пользователя отключена двухфакторная аутентификация через Google",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Двухфакторная авторизация через Google Authenticator отключена"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Не найден токен двухфакторной авторизации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Отсутствует запись о токене двухфакторной авторизации"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Неверный код от Google Authenticator",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Неверный код двухфакторной авторизации через Google Authenticator"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации полей запроса",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"code": "The code is required."})
     *         )
     *     )
     * )
     */
    public function verifyCodeGoogle2fa(CodeGoogleAuthenticatorRequest $request)
    {
        $hashedToken = $this->generationTwoFactorAuthorizationToken->hashToken($request->token);
        $tokenInfo = $this->twoFactorAuthorizationRepository->getTokenWithUser($hashedToken);
        if($tokenInfo === null)
        {
            return ApiResponse::error('Отсутствует запись о токене двухфакторной авторизации', null, 404);
        }
        $user = $tokenInfo->user;
        if($user->google2fa_enable === false)
        {
            return ApiResponse::success('Двухфакторная авторизация через Google Authenticator отключена',null, 409 );
        }
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey(
            Crypt::decrypt($user->google2fa_secret),
            $request->code
        );
        if (!$valid) {
            return ApiResponse::error('Неверный код двухфакторной авторизации через Google Authenticator', null, 403);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

    /**
     * @OA\Post(
     *     path="/twoFactorVerification/useRecoveryCode",
     *     operationId="useRecoveryCode",
     *     tags={"Двухфакторная аутентификация"},
     *     summary="Использование кода восстановления двухфакторной авторизации",
     *     description="Позволяет пройти двухфакторную авторизацию с использованием кода восстановления.",
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UseRecoveryCodeRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешная авторизация",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно прошёл двухфакторную авторизацию"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJh...")
     *             )
     *         ),
     *         headers={
     *             @OA\Header(
     *                 header="Set-Cookie",
     *                 description="Устанавливает refresh_token в cookie",
     *                 @OA\Schema(type="string", example="refresh_token=...; HttpOnly; Path=/; Max-Age=3600")
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Токен или код восстановления не найдены",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Отсутствует запись о токене двухфакторной авторизации"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступных кодов восстановления",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Для текущего пользователя не осталось активных кодов восстановления"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="У пользователя отключена двухфакторная авторизация",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Для данного пользователя отключена двухфакторная авторизация"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="token",
     *                     type="array",
     *                     @OA\Items(type="string", example="Token is required")
     *                 ),
     *                 @OA\Property(
     *                     property="recovery_code",
     *                     type="array",
     *                     @OA\Items(type="string", example="Recovery code must be a string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function useRecoveryCode(UseRecoveryCodeRequest $request)
    {
        $hashedToken = $this->generationTwoFactorAuthorizationToken->hashToken($request->token);
        $tokenInfo = $this->twoFactorAuthorizationRepository->getTokenWithUser($hashedToken);
        if($tokenInfo === null)
        {
            return ApiResponse::error('Отсутствует запись о токене двухфакторной авторизации', null, 404);
        }
        $user = $tokenInfo->user;
        if($user->google2fa_enable === false && $user->two_factor_email_enabled === false)
        {
            return ApiResponse::error('Для данного пользователя отключена двухфакторная авторизация', null, 409);
        }
        $countRecoveryCode = $this->recoveryCodeRepository->getCountActiveRecoveryCodeForUser($user->id);
        if($countRecoveryCode === 0)
        {
            return ApiResponse::error('Для текущего пользователя не осталось активных кодов восстановления',null, 403);
        }
        $hashedRecoveryCode = $this->generationRecoveryCodeService->hashRecoveryCode($request->recovery_code);
        $recoveryCode = $this->recoveryCodeRepository->getRecoveryCodeForUser($user->id, $hashedRecoveryCode);
        if($recoveryCode === null)
        {
            return ApiResponse::error('Не найден данный резервный код для пользователя',null, 404);
        }
        $this->recoveryCodeRepository->deleteRecoveryCode($user->id, $hashedRecoveryCode);
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

    /**
     * @OA\Post(
     *     path="/twoFactorVerification/refreshRecoveryCodes",
     *     operationId="refreshRecoveryCodes",
     *     tags={"Двухфакторная аутентификация"},
     *     summary="Обновить резервные коды двухфакторной авторизации",
     *     description="Обновляет резервные коды для авторизованного пользователя, если двухфакторная авторизация включена и все старые коды использованы.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление резервных кодов",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Новые резервные коды"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="recovery_codes",
     *                     type="array",
     *                     description="Список новых резервных кодов",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Двухфакторная авторизация отключена для пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Для данного пользователя отключена двухфакторная авторизация, поэтому обновление резервных кодов невозможно"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь еще не использовал все ранее предоставленные коды",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Текущий авторизованный пользователь не использовал все ранее предоставленные резервные коды"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized")
     * )
     */
    public function refreshRecoveryCodes()
    {
        $authUser = auth()->user();
        if($authUser->google2fa_enable === false && $authUser->two_factor_email_enabled === false)
        {
            return ApiResponse::error('Для данного пользователя отключена двухфакторная авторизация, поэтому обновление резервных кодов невозможно', null, 404);
        }
        $countRecoveryCode = $this->recoveryCodeRepository->getCountActiveRecoveryCodeForUser(auth()->id());
        if($countRecoveryCode !== 0)
        {
            return ApiResponse::error('Текущий авторизованный пользователь не использовал все ранее предоставленные резервные коды',null, 409);
        }
        $recoveryCodes = $this->createRecoveryCodesForUser($authUser->id);
        return ApiResponse::success('Новые резервные коды', (object)['recovery_codes'=>$recoveryCodes]);
    }

    // TODO перенести в какой-то другой файл
    private function createRecoveryCodesForUser(int $userId): array
    {
        $this->recoveryCodeRepository->deleteRecoveryCodesForUser($userId);
        $countRecoveryCodes = config('app.count_recovery_codes');
        $this->generationRecoveryCodeService->setUserId($userId);
        $recoveryCodes = [];
        for($indexCurrentRecoveryCode = 0; $indexCurrentRecoveryCode < $countRecoveryCodes; $indexCurrentRecoveryCode++)
        {
            $recoveryCodeData = $this->generationRecoveryCodeService->generateRecoveryCode();
            if($recoveryCodeData === null)
            {
                continue;
            }
            $recoveryCode = $recoveryCodeData['recoveryCode'];
            $hashedRecoveryCode = $recoveryCodeData['hashedRecoveryCode'];
            $this->recoveryCodeRepository->saveRecoveryCode($userId, $hashedRecoveryCode);
            $recoveryCodes[] = $recoveryCode;
        }
        return $recoveryCodes;
    }
}
