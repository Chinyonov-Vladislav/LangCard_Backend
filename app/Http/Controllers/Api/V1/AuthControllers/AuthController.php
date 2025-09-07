<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Enums\TypeRequestApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\InviteCodeMail;
use App\Mail\PasswordForAccountCreatedByOauthMail;
use App\Models\User;
use App\Repositories\AuthTokenRepositories\AuthTokenRepositoryInterface;
use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepository;
use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use App\Repositories\UserProviderRepositories\UserProviderRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\AchievementService;
use App\Services\ApiServices\ApiService;
use App\Services\CookieService;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use App\Services\GeneratingPasswordService;
use App\Services\GenerationCodeServices\GenerationAuthTokenService;
use App\Services\GenerationCodeServices\GenerationInviteCodeService;
use App\Services\GenerationCodeServices\GenerationTwoFactorAuthorizationToken;
use App\Services\NicknameExtractorFromEmailService;
use Carbon\Carbon;
use Exception;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    protected UserProviderRepositoryInterface $userProviderRepository;
    protected ApiService $apiService;

    protected GenerationAuthTokenService $generationAuthTokenService;

    protected GenerationInviteCodeService $generationInviteCodeService;

    protected GenerationTwoFactorAuthorizationToken $generationTwoFactorAuthorizationToken;

    protected CookieService $cookieService;

    protected AchievementService $achievementService;

    private array $acceptedProviders = ['google', 'yandex', 'microsoft'];

    private array $acceptedCallbackProviders = ['google', 'yandex', 'microsoft'];

    protected GeneratingPasswordService $generatingPasswordService;


    public function __construct(LoginRepositoryInterface                  $loginRepository,
                                RegistrationRepositoryInterface           $registrationRepository,
                                UserRepositoryInterface                   $userRepository,
                                AuthTokenRepositoryInterface              $authTokenRepository,
                                EmailVerificationCodeRepository           $emailVerificationCodeRepository,
                                InviteCodeRepositoryInterface             $inviteCodeRepository,
                                TwoFactorAuthorizationRepositoryInterface $twoFactorAuthorizationRepository,
                                UserProviderRepositoryInterface           $userProviderRepository)
    {
        $this->loginRepository = $loginRepository;
        $this->registrationRepository = $registrationRepository;
        $this->userRepository = $userRepository;
        $this->authTokenRepository = $authTokenRepository;
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
        $this->inviteCodeRepository = $inviteCodeRepository;
        $this->twoFactorAuthorizationRepository = $twoFactorAuthorizationRepository;
        $this->userProviderRepository = $userProviderRepository;
        $this->apiService = app(ApiService::class);
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
        $this->generationAuthTokenService = new GenerationAuthTokenService();
        $this->nicknameExtractorFromEmailService = new NicknameExtractorFromEmailService();
        $this->generationInviteCodeService = new GenerationInviteCodeService();
        $this->cookieService = new CookieService();
        $this->generationTwoFactorAuthorizationToken = new GenerationTwoFactorAuthorizationToken();
        $this->achievementService = new AchievementService();
        $this->generatingPasswordService = new GeneratingPasswordService();
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Authenticate user",
     *     description="Authenticates user with email/password. Returns access tokens or 2FA data if enabled.",
     *     tags={"Аутентификация"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
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
     *                     @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="access_token", type="string"),
     *                         @OA\Property(property="email_is_verified", type="boolean")
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", enum={"success", "error"}, example="success"),
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
     *             @OA\Property(property="status", enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
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
            return ApiResponse::error(__('api.wrong_login_or_password'), null, 404);
        }
        if ($user->two_factor_email_enabled || $user->google2fa_enable) {
            $tokenData = $this->generationTwoFactorAuthorizationToken->generateTwoFactorAuthorizationToken();
            $this->twoFactorAuthorizationRepository->updateOrSaveTwoFactorAuthorizationCode($tokenData['hashedToken'], $user->id);
            return ApiResponse::success('Включена двухфакторная авторизация', (object)['two_factor_email_enabled' => $user->two_factor_email_enabled,
                'two_factor_google_authenticator_enabled' => $user->google2fa_enable, 'two_factor_token' => $tokenData['token']]);
        }
        $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
        $arrayTokens = $this->generationAuthTokenService->generateTokens($user, $countMinutesExpirationRefreshToken);
        $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);

        //$cookieForRefreshToken = $this->cookieService->getCookieForRefreshTokenWithPartitioned($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
        return ApiResponse::success(__('api.success_authorization_email'), (object)['access_token' => $arrayTokens['access_token'],
            'email_is_verified' => $user->email_verified_at !== null])->withCookie($cookieForRefreshToken);
        //$response->headers->set('Set-Cookie', $cookieForRefreshToken, false);
    }


    /**
     * @OA\Get(
     *     path="/auth/{provider}/redirect",
     *     summary="Получить OAuth redirect URL для провайдера",
     *     tags={"Аутентификация через OAuth"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Провайдер OAuth аутентификации",
     *         @OA\Schema(
     *             type="string",
     *             enum={"google", "yandex", "microsoft"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL редиректа получен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="OAuth URL получен успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="url", type="string", example="https://accounts.google.com/o/oauth2/auth?...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Неподдерживаемый провайдер",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Провайдер 'facebook' не поддерживается."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function redirect($provider)
    {
        if (!in_array($provider, $this->acceptedProviders)) {
            return ApiResponse::error(__('api.auth_provider_not_supported', ['provider' => $provider]), null, 404);
        }
        $url = Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
        return ApiResponse::success(__('api.getting_oauth_url', ['provider' => $provider]), (object)['url' => $url]);
    }


    /**
     * @OA\Get(
     *     path="/auth/{provider}/callback",
     *     summary="OAuth Callback от провайдера",
     *     description="Обработка callback-аутентификации от OAuth-провайдера. Возвращает access_token или two_factor_token. Устанавливает refresh_token в cookie.",
     *     tags={"Аутентификация через OAuth"},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Провайдер OAuth-аутентификации",
     *         @OA\Schema(
     *             type="string",
     *             enum={"google", "yandex", "microsoft"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная авторизация",
     *         @OA\JsonContent(
     *                @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *                @OA\Property(property="message", type="string", example="Авторизация прошла успешно."),
     *                @OA\Property(
     *                    property="data",
     *                    type="object",
     *                    @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1..."),
     *                    @OA\Property(property="email_is_verified", type="boolean", example="true"),
     *               )
     *         ),
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="Refresh токен устанавливается в cookie",
     *             @OA\Schema(type="string", example="refresh_token=abc123; HttpOnly; Secure")
     *         )
     *     ),
     *     @OA\Response(
     *          response=202,
     *          description="Требуется двухфакторная авторизация",
     *          @OA\JsonContent(
     *                 @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *                 @OA\Property(property="message", type="string", example="Включена двухфакторная авторизация"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(property="two_factor_email_enabled", type="boolean", example="true"),
     *                     @OA\Property(property="two_factor_google_authenticator_enabled", type="boolean", example="true"),
     *                     @OA\Property(property="two_factor_token", type="string", example="eyJhbGciOiJIUzI1..."),
     *                )
     *          ),
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Провайдер не поддерживается",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "data"},
     *             @OA\Property(property="status", enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="Провайдер 'facebook' не поддерживается."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка при аутентификации",
     *         @OA\JsonContent(
     *             type="object",
     *              required={"status", "message", "errors"},
     *             @OA\Property(property="status", enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Произошла ошибка при авторизации через yandex."),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function handleCallback($provider, Request $request)
    {
        try {
            if (!in_array($provider, $this->acceptedCallbackProviders)) {
                return ApiResponse::error(__('api.auth_provider_not_supported', ['provider' => $provider]), null, 404);
            }
            $isNewUser = false;
            // TODO: добавить авторизацию через telegram
            if ($provider === 'microsoft') {
                $microsoftUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $microsoftUser->id;
                $userProviderData = $this->userProviderRepository->getDataProviderWithUser($providerId, $provider);
                if ($userProviderData === null) {

                    $idToken = $microsoftUser->accessTokenResponseBody['id_token']; // берём id_token из ответа
                    $parts = explode('.', $idToken);
                    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                    $email = $payload['email'] ?? $payload['preferred_username'] ?? null;
                    $user = $this->loginRepository->getUserByEmail($email);
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
                        $password = $this->generatingPasswordService->generatePassword();
                        $user = $this->registrationRepository->registerUser(name: $nickname, email: $email, password: $password, avatar_url: $pathToAvatar);
                        Mail::to($user->email)->queue(new PasswordForAccountCreatedByOauthMail($provider, $user->email, $password));
                        $isNewUser = true;
                    }
                    $newUserProvider = $this->userProviderRepository->saveUserProvider($user->id, $providerId, $provider);
                } else {
                    $user = $userProviderData->user;
                }
            } else if ($provider == 'yandex') {
                $yandexUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $yandexUser->id;
                $userProviderData = $this->userProviderRepository->getDataProviderWithUser($providerId, $provider);
                if ($userProviderData === null) {
                    $user = $this->loginRepository->getUserByEmail($yandexUser->email);
                    if ($user === null) {
                        $nickname = $this->nicknameExtractorFromEmailService->extractNicknameFromEmail($yandexUser->email);
                        $pathToAvatar = null;
                        if ($yandexUser->avatar !== null) {
                            $avatar = $this->downloadFileService->downloadFile($yandexUser->avatar);
                            $pathToAvatar = $this->saveFileService->saveFile($avatar);
                        }
                        $password = $this->generatingPasswordService->generatePassword();
                        $user = $this->registrationRepository->registerUser(name: $nickname, email: $yandexUser->email, password: $password, avatar_url: $pathToAvatar);
                        Mail::to($user->email)->queue(new PasswordForAccountCreatedByOauthMail($provider, $user->email, $password));
                        $isNewUser = true;
                    }
                    $newUserProvider = $this->userProviderRepository->saveUserProvider($user->id, $providerId, $provider);
                    /*$timezoneId = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::timezoneRequest);
                    $currencyIdFromDatabase = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::currencyRequest);
                    $this->userRepository->updateTimezoneId($user, $timezoneId);
                    $this->userRepository->updateCurrencyId($user, $currencyIdFromDatabase);*/
                } else {
                    $user = $userProviderData->user;
                }
            } else // авторизация через гугл
            {
                $googleUser = Socialite::driver($provider)->stateless()->user();
                $providerId = $googleUser->getId();
                $userProviderData = $this->userProviderRepository->getDataProviderWithUser($providerId, $provider);
                if ($userProviderData === null) {
                    $user = $this->loginRepository->getUserByEmail($googleUser->getEmail());
                    if ($user === null) {
                        $nickname = $this->nicknameExtractorFromEmailService->extractNicknameFromEmail($googleUser->getEmail());
                        $avatarURL = $googleUser->getAvatar();
                        $pathToAvatar = null;
                        if ($avatarURL !== null) {
                            $avatar = $this->downloadFileService->downloadFile($avatarURL);
                            $pathToAvatar = $this->saveFileService->saveFile($avatar);
                        }
                        $password = $this->generatingPasswordService->generatePassword();
                        $user = $this->registrationRepository->registerUser(name: $nickname, email: $googleUser->getEmail(), password: $password, avatar_url: $pathToAvatar);
                        Mail::to($user->email)->queue(new PasswordForAccountCreatedByOauthMail($provider, $user->email, $password));
                        $isNewUser = true;
                    }
                    $newUserProvider = $this->userProviderRepository->saveUserProvider($user->id, $providerId, $provider);
                    /*$timezoneId = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::timezoneRequest);
                    $currencyIdFromDatabase = $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::currencyRequest);
                    $this->userRepository->updateTimezoneId($user, $timezoneId);
                    $this->userRepository->updateCurrencyId($user, $currencyIdFromDatabase);*/
                } else {
                    $user = $userProviderData->user;
                }
            }
            if ($isNewUser) {
                $this->apiService->makeRequest($request->ip(), $user->id, TypeRequestApi::allRequests);
                $this->achievementService->startAchievementsForNewUser($user->id);
                $user = $this->emailVerificationCodeRepository->verificateEmailAddressForUser($user);
                $code = $this->generationInviteCodeService->generateInviteCode();
                $this->inviteCodeRepository->saveInviteCode($user->id, $code);
            }
            if ($user->two_factor_email_enabled || $user->google2fa_enable) {
                $tokenData = $this->generationTwoFactorAuthorizationToken->generateTwoFactorAuthorizationToken();
                $this->twoFactorAuthorizationRepository->updateOrSaveTwoFactorAuthorizationCode($tokenData['hashedToken'], $user->id);
                return ApiResponse::success('Включена двухфакторная авторизация', (object)['two_factor_email_enabled' => $user->two_factor_email_enabled,
                    'two_factor_google_authenticator_enabled' => $user->google2fa_enable, 'two_factor_token' => $tokenData['token']],202);
            }
            $countMinutesExpirationRefreshToken = config('sanctum.expiration_refresh_token');
            $arrayTokens = $this->generationAuthTokenService->generateTokens($user, $countMinutesExpirationRefreshToken);
            $cookieForRefreshToken = $this->cookieService->getCookieForRefreshToken($arrayTokens['refresh_token'], $countMinutesExpirationRefreshToken);
            return ApiResponse::success(__('api.success_authorization_with_oauth'), (object)['access_token' => $arrayTokens['access_token'],
                "email_is_verified"=>$user->email_verified_at !== null])->withCookie($cookieForRefreshToken);
        }
        catch (Exception $exception)
        {
            logger($exception);
            return ApiResponse::error(__("api.common_mistake_authorization_with_oauth", ["provider"=>$provider]), null,500);
        }
    }

    /**
     * @OA\Post(
     *     path="/refresh",
     *     summary="Обновление access и refresh токенов",
     *     description="Обновляет access токен и refresh токен пользователя, используя refresh token из cookie",
     *     operationId="refreshTokens",
     *     tags={"Обновление токенов авторизации"},
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Parameter(
     *         name="refresh_token",
     *         in="cookie",
     *         required=true,
     *         description="Refresh токен, передаваемый через cookie",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Токены успешно обновлены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="success"),
     *             @OA\Property(property="message", type="string", example="Access и Refresh токены успешно обновлены"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Невалидный refresh токен или отсутствует токен",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Невалидный refresh токен"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Недопустимый тип владельца токена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string",enum={"success", "error"}, example="error"),
     *             @OA\Property(property="message", type="string", example="Недопустимый тип владельца токена"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        if ($refreshToken === null) {
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

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Выход из системы (отзыв токенов)",
     *     description="Удаляет access и refresh токены текущего авторизованного пользователя. Требуется Bearer токен.",
     *     operationId="logoutUser",
     *     tags={"Выход из аккаунта"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный выход из системы",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно вышел из системы"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized")
     * )
     */
    public function logout()
    {
        $accessTokenId = auth()->user()->currentAccessToken()->id;
        auth()->user()->currentAccessToken()->delete();
        $this->authTokenRepository->deleteRefreshTokenForUserByIdAccessToken($accessTokenId);
        return ApiResponse::success(__('api.success_logout'))->withoutCookie('refresh_token');
    }
}
