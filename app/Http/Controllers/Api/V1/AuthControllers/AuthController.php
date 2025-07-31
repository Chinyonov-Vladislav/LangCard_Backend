<?php

namespace App\Http\Controllers\Api\V1\AuthControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequests\AuthRequest;
use App\Http\Requests\Api\V1\AuthTokenRequests\RefreshTokenRequest;
use App\Http\Responses\ApiResponse;
use App\Mail\InviteCodeMail;
use App\Models\User;
use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepository;
use App\Repositories\InviteCodeRepositories\InviteCodeRepository;
use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\AuthTokenRepositories\AuthTokenRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use App\Services\GenerationAuthTokenService;
use App\Services\GenerationInviteCodeService;
use App\Services\NicknameExtractorFromEmailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use function PHPUnit\Framework\objectEquals;

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

    protected InviteCodeRepositoryInterface $inviteCodeRepository;
    protected ApiService $apiService;

    protected GenerationAuthTokenService $generationAuthTokenService;

    protected GenerationInviteCodeService $generationInviteCodeService;

    private array $acceptedProviders = ['google', 'yandex', 'microsoft'];

    private array $acceptedCallbackProviders = ['google', 'yandex', 'microsoft'];


    public function __construct(LoginRepositoryInterface        $loginRepository,
                                RegistrationRepositoryInterface $registrationRepository,
                                UserRepositoryInterface         $userRepository,
                                AuthTokenRepositoryInterface    $authTokenRepository,
                                EmailVerificationCodeRepository $emailVerificationCodeRepository,
                                InviteCodeRepositoryInterface   $inviteCodeRepository)
    {
        $this->loginRepository = $loginRepository;
        $this->registrationRepository = $registrationRepository;
        $this->userRepository = $userRepository;
        $this->authTokenRepository = $authTokenRepository;
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
        $this->inviteCodeRepository = $inviteCodeRepository;
        $this->apiService = app(ApiService::class);
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
        $this->generationAuthTokenService = new GenerationAuthTokenService();
        $this->nicknameExtractorFromEmailService = new NicknameExtractorFromEmailService();
        $this->generationInviteCodeService = new GenerationInviteCodeService();
    }

    public function login(AuthRequest $request)
    {
        $user = $this->loginRepository->getUserByEmail($request->email);
        if ($user->password === null || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(__('api.user_not_found_by_email'), null, 401);
        }
        $arrayTokens = $this->generateTokens($user);
        return ApiResponse::success(__('api.success_authorization_email'), (object)['access_token' => $arrayTokens['access_token'],
            'refresh_token' => $arrayTokens['refresh_token'], 'email_is_verified' => $user->email_verified_at !== null]);
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
            $arrayTokens = $this->generateTokens($user);
            return ApiResponse::success(__('api.success_authorization_with_oauth'), (object)$arrayTokens);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            return ApiResponse::error(__('api.common_mistake_authorization_with_oauth', ['provider' => $provider]), null, 500);
        }

    }

    public function refresh(RefreshTokenRequest $request)
    {
        $hashedToken = $this->generationAuthTokenService->hashToken($request->refresh_token);
        $tokenInfo = $this->authTokenRepository->getRefreshToken($hashedToken);
        if ($tokenInfo === null || Carbon::parse($tokenInfo->expires_at)->isPast()) {
            return ApiResponse::error('Невалидный refresh токен', null, 401);
        }
        if (!($tokenInfo->personalAccessToken->tokenable instanceof User)) {
            return ApiResponse::error('Недопустимый тип владельца токена', null, 403);
        }
        $user = $tokenInfo->personalAccessToken->tokenable;
        $this->authTokenRepository->deleteAccessTokenById($tokenInfo->personalAccessToken->id);
        $arrayTokens = $this->generateTokens($user);
        return ApiResponse::success(__('Access и Refresh токены успешно обновлены'), (object)$arrayTokens);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return ApiResponse::error(__('api.success_logout'));
    }

    private function generateTokens(User $user): array
    {
        $token = $user->createToken('api-token');
        $dataRefreshToken = $this->generationAuthTokenService->generateRefreshToken();
        $expirationDate = Carbon::now()->addMinutes(config('sanctum.expiration_refresh_token'));
        $this->authTokenRepository->saveRefreshToken($dataRefreshToken['hashedToken'], $expirationDate, $token->accessToken->id);
        return ['access_token' => $token->plainTextToken, 'refresh_token' => $dataRefreshToken['token']];
    }
}
