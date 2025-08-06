<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Repositories\AuthTokenRepositories\AuthTokenRepositoryInterface;
use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepository;
use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use App\Services\CookieService;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use App\Services\GenerationCodeServices\GenerationAuthTokenService;
use App\Services\GenerationCodeServices\GenerationInviteCodeService;
use App\Services\GenerationCodeServices\GenerationTwoFactorAuthorizationToken;
use App\Services\NicknameExtractorFromEmailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    protected NicknameExtractorFromEmailService $nicknameExtractorFromEmailService;
    protected DownloadFileService $downloadFileService;
    protected SaveFileService $saveFileService;
    protected LoginRepositoryInterface $loginRepository;
    protected RegistrationRepositoryInterface $registrationRepository;
    protected UserRepositoryInterface $userRepository;
    protected EmailVerificationCodeRepository $emailVerificationCodeRepository;

    protected AuthTokenRepositoryInterface $authTokenRepository;
    protected TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository;

    protected InviteCodeRepositoryInterface $inviteCodeRepository;
    protected ApiService $apiService;

    protected GenerationAuthTokenService $generationAuthTokenService;

    protected GenerationInviteCodeService $generationInviteCodeService;

    protected GenerationTwoFactorAuthorizationToken $generationTwoFactorAuthorizationToken;

    protected CookieService $cookieService;

    private array $acceptedProviders = ['google', 'yandex', 'microsoft'];

    private array $acceptedCallbackProviders = ['google', 'yandex', 'microsoft'];


    public function __construct(LoginRepositoryInterface        $loginRepository,
                                RegistrationRepositoryInterface $registrationRepository,
                                UserRepositoryInterface         $userRepository,
                                AuthTokenRepositoryInterface    $authTokenRepository,
                                EmailVerificationCodeRepository $emailVerificationCodeRepository,
                                InviteCodeRepositoryInterface   $inviteCodeRepository,
                                TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository)
    {
        $this->loginRepository = $loginRepository;
        $this->registrationRepository = $registrationRepository;
        $this->userRepository = $userRepository;
        $this->authTokenRepository = $authTokenRepository;
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
        $this->inviteCodeRepository = $inviteCodeRepository;
        $this->twoFactorAuthorizationRepository = $twoFactorAuthorizationRepository;
        $this->apiService = app(ApiService::class);
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
        $this->generationAuthTokenService = new GenerationAuthTokenService();
        $this->nicknameExtractorFromEmailService = new NicknameExtractorFromEmailService();
        $this->generationInviteCodeService = new GenerationInviteCodeService();
        $this->cookieService = new CookieService();
        $this->generationTwoFactorAuthorizationToken = new GenerationTwoFactorAuthorizationToken();
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Authenticate user",
     *     description="Authenticates user with email/password. Returns access tokens or 2FA data if enabled.",
     *     tags={"Аутентификация"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(ref="#/components/schemas/AuthRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="access_token", type="string"),
     *                         @OA\Property(property="email_is_verified", type="boolean")
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="two_factor_email_enabled", type="boolean"),
     *                         @OA\Property(property="two_factor_google_authenticator_enabled", type="boolean"),
     *                         @OA\Property(property="two_factor_token", type="string")
     *                     ),
     *                     @OA\Property(property="message", type="string", example="Включена двухфакторная авторизация")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The email field is required.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="The password field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function login(AuthRequest $request)
    {
        $user = $this->loginRepository->getUserByEmail($request->email);
        if ($user->email === null || $user->password === null || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(__('api.user_not_found_by_email'), null, 404);
        }
        if($user->two_factor_email_enabled || $user->google2fa_enable)
        {
            $tokenData = $this->generationTwoFactorAuthorizationToken->generateTwoFactorAuthorizationToken();
            $this->twoFactorAuthorizationRepository->updateOrSaveTwoFactorAuthorizationCode($tokenData['hashedToken'], $user->id);
            return ApiResponse::success('Включена двухфакторная авторизация', (object)['two_factor_email_enabled'=>$user->two_factor_email_enabled,
                'two_factor_google_authenticator_enabled'=>$user->google2fa_enable, 'two_factor_token'=>$tokenData['token']]);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);

        //$cookieForRefreshToken = $this->cookieService->getCookieForRefreshTokenWithPartitioned($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        return ApiResponse::success(__('api.success_authorization_email'), (object)['access_token' => $arrayTokens['access_token'],
            'email_is_verified' => $user->email_verified_at !== null])->withCookie($cookieForRefreshToken);
        //$response->headers->set('Set-Cookie', $cookieForRefreshToken, false);
    }

    public function redirect($provider)
    {
        if (!in_array($provider, $this->acceptedProviders)) {
            return ApiResponse::error(__('api.auth_provider_not_supported', ['provider' => $provider]), null, 401);
        }
        $url = Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
        return ApiResponse::success(__('api.getting_oauth_url', ['provider' => $provider]), (object)['url' => $url]);
    }

    public function handleCallback($provider)
    {
        try {
            if (!in_array($provider, $this->acceptedCallbackProviders)) {
                return ApiResponse::error(__('api.auth_provider_not_supported', ['provider' => $provider]), null, 401);
            }
            // TODO: добавить авторизацию через telegram
            if ($provider === 'microsoft') {
                $microsoftUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $microsoftUser->id;
                $user = $this->loginRepository->getUserByProviderAndProviderId($providerId, $provider);
                if ($user === null) {
                    if ($microsoftUser->nickname !== null) {
                        $nickname = $microsoftUser->nickname;
                    } else {
                        $nickname = $this->nicknameExtractorFromEmailService->extractNicknameFromEmail($microsoftUser->email);
                    }
                    $pathToAvatar = null;
                    if ($microsoftUser->avatar !== null) {
                        $avatar = $this->downloadFileService->downloadFile($microsoftUser->avatar);
                        $pathToAvatar = $this->saveFileService->saveFile($avatar);
                    }
                    $user = $this->registrationRepository->registerUser($nickname, null, null, null, null, $pathToAvatar, 'user', null, $providerId, $provider);
                }
            }
            else if ($provider == 'yandex') {
                $yandexUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $yandexUser->id;
                $user = $this->loginRepository->getUserByProviderAndProviderId($providerId, $provider);
                if ($user === null) {
                    $nickname = $this->nicknameExtractorFromEmailService->extractNicknameFromEmail($yandexUser->email);
                    $pathToAvatar = null;
                    if ($yandexUser->avatar !== null) {
                        $avatar = $this->downloadFileService->downloadFile($yandexUser->avatar);
                        $pathToAvatar = $this->saveFileService->saveFile($avatar);
                    }
                    $user = $this->registrationRepository->registerUser($nickname, null, null, null, null, $pathToAvatar, 'user', null, $providerId, $provider);
                    /*$timezoneId = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::timezoneRequest);
                    $currencyIdFromDatabase = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::currencyRequest);
                    $this->userRepository->updateTimezoneId($user, $timezoneId);
                    $this->userRepository->updateCurrencyId($user, $currencyIdFromDatabase);*/
                }
            }
            else // авторизация через гугл
            {
                $googleUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $googleUser->getId();
                $user = $this->loginRepository->getUserByProviderAndProviderId($providerId, $provider);
                if ($user === null) {
                    $nickname = $this->nicknameExtractorFromEmailService->extractNicknameFromEmail($googleUser->getEmail());
                    $avatarURL = $googleUser->getAvatar();
                    $pathToAvatar = null;
                    if ($avatarURL !== null) {
                        $avatar = $this->downloadFileService->downloadFile($avatarURL);
                        $pathToAvatar = $this->saveFileService->saveFile($avatar);
                    }
                    $user = $this->registrationRepository->registerUser($nickname, null, null, null, null, $pathToAvatar, 'user', null, $providerId, $provider);
                    /*$timezoneId = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::timezoneRequest);
                    $currencyIdFromDatabase = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::currencyRequest);
                    $this->userRepository->updateTimezoneId($user, $timezoneId);
                    $this->userRepository->updateCurrencyId($user, $currencyIdFromDatabase);*/

                }
            }
            if ($user->email_verified_at === null) {
                $this->emailVerificationCodeRepository->verificateEmailAddress($user->id);
            }
            if (!$this->userRepository->hasUserInviteCode(auth()->user()->id)) {
                $code = $this->generationInviteCodeService->generateInviteCode();
                $this->inviteCodeRepository->saveInviteCode(auth()->user()->id, $code);
            }
            $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
            $arrayTokens = $this->generationAuthTokenService->generateTokens($user, $countMinutesExpirationRefreshToken);
            $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
            return ApiResponse::success(__('api.success_authorization_with_oauth'), (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            return ApiResponse::error(__('api.common_mistake_authorization_with_oauth', ['provider' => $provider]), null, 500);
        }

    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        if($refreshToken === null)
        {
            return ApiResponse::error('Невалидный refresh токен', null, 401);
        }
        $hashedToken = $this->generationAuthTokenService->hashToken($refreshToken);
        $tokenInfo = $this->authTokenRepository->getRefreshToken($hashedToken);
        if ($tokenInfo === null || Carbon::parse($tokenInfo->expires_at)->isPast()) {
            return ApiResponse::error('Невалидный refresh токен', null, 401);
        }
        if (!($tokenInfo->personalAccessToken->tokenable instanceof User)) {
            return ApiResponse::error('Недопустимый тип владельца токена', null, 403);
        }
        $user = $tokenInfo->personalAccessToken->tokenable;
        $this->authTokenRepository->deleteAccessTokenById($tokenInfo->personalAccessToken->id);
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        return ApiResponse::success('Access и Refresh токены успешно обновлены', (object)['access_token' => $arrayTokens['access_token']])->withCookie($cookieForRefreshToken);
    }

    public function logout()
    {
        $accessTokenId = auth()->user()->currentAccessToken()->id;
        auth()->user()->currentAccessToken()->delete();
        $this->authTokenRepository->deleteRefreshTokenForUserByIdAccessToken($accessTokenId);
        return ApiResponse::success(__('api.success_logout'))->withoutCookie('refresh_token');
    }
}
