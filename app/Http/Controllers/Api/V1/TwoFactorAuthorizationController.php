<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeTwoFactorAuthorization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\ConfirmationCodeEmailTwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\EnableDisableATwoFactorAuthorizationRequest;
use App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests\TwoFactorAuthorizationTokenRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\EmailTwoFactorAuthorizationMail;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use App\Services\CookieService;
use App\Services\GenerationCodeServices\GenerationAuthTokenService;
use App\Services\GenerationCodeServices\GenerationTwoFactorAuthorizationToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthorizationController extends Controller
{
    protected TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository;
    protected LoginRepositoryInterface $loginRepository;
    protected GenerationAuthTokenService $generationAuthTokenService;
    protected CookieService $cookieService;

    protected GenerationTwoFactorAuthorizationToken $generationTwoFactorAuthorizationToken;

    public function __construct(TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository, LoginRepositoryInterface $loginRepository)
    {
        $this->twoFactorAuthorizationRepository = $twoFactorAuthorizationRepository;
        $this->loginRepository = $loginRepository;
        $this->generationAuthTokenService = new GenerationAuthTokenService();
        $this->cookieService = new CookieService();
        $this->generationTwoFactorAuthorizationToken = new GenerationTwoFactorAuthorizationToken();
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
                return ApiResponse::success('Переключено состояние двухфакторной авторизации через электронную почту', (object)['two_factor_email'=>$authUser->two_factor_email_enabled]);
            case TypeTwoFactorAuthorization::googleAuthenticator->value:
                return;
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
        Mail::to($tokenInfo->user->email)->send(new EmailTwoFactorAuthorizationMail($code, $countMinutes));
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
        $expirationDate = Carbon::parse($tokenInfo->user->two_factor_code_expiration_date);
        if($expirationDate->isPast() || $request->code !== $tokenInfo->user->two_factor_code_email)
        {
            return ApiResponse::error('Код не валиден. Запросите новый код',null, 401);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($tokenInfo->user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        $this->twoFactorAuthorizationRepository->deleteToken($hashedToken);
        return ApiResponse::success('Пользователь успешно прошёл двухфакторную авторизацию',
            (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

}
