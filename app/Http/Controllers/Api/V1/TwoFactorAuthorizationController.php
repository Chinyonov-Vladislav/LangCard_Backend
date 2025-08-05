<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeTwoFactorAuthorization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\CodeGoogleAuthenticatorRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\ConfirmationCodeEmailTwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\EnableDisableATwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\TwoFactorAuthorizationTokenRequest;
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
            return ApiResponse::success('Для текущего пользователя отключена двухфакторная авторизация',null, 409 );
        }
        if($tokenInfo->user->two_factor_code_email === null || $tokenInfo->user->two_factor_code_email_expiration_date === null)
        {
            return ApiResponse::error('Текущий пользователь не запрашивал код для двухфакторной авторизации по электронной почте');
        }
        $expirationDate = Carbon::parse($tokenInfo->user->two_factor_code_email_expiration_date);
        if($expirationDate->isPast())
        {
            return ApiResponse::error('Срок действия кода истёк. Запросите новый код',null, 422);
        }
        if($request->code !== $tokenInfo->user->two_factor_code_email)
        {
            return ApiResponse::error('Введенный код некорректен! Повторите попытку ввода!',null, 422);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

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
            return ApiResponse::error('Неверный код двухфакторной авторизации через Google Authenticator', null, 422);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
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
