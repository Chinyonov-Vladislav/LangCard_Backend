<?php

namespace App\Services\GenerationCodeServices;

use App\Models\User;
use App\Repositories\AuthTokenRepositories\AuthTokenRepositoryInterface;
use Carbon\Carbon;
use Str;

class GenerationAuthTokenService
{
    protected AuthTokenRepositoryInterface $authTokenRepository;

    public function __construct()
    {
        $this->authTokenRepository = app(AuthTokenRepositoryInterface::class);
    }

    public function generateRefreshToken(): array
    {
        do
        {
            $plainTextToken = Str::random(64); // исходный токен
            $hashedToken = $this->hashToken($plainTextToken);
        }
        while($this->authTokenRepository->isExistRefreshToken($hashedToken));
        return ['token'=>$plainTextToken, 'hashedToken'=>$hashedToken];
    }

    public function hashToken(string $plainTextToken): string
    {
        return hash_hmac('sha256', $plainTextToken, config('sanctum.jwt_secret')); // хешируем токен
    }

    public function generateTokens(User $user, int $countMinutesExpirationRefreshToken): array
    {
        $token = $user->createToken('api-token');
        $dataRefreshToken = $this->generateRefreshToken();
        $expirationDate = Carbon::now()->addMinutes($countMinutesExpirationRefreshToken);
        $this->authTokenRepository->saveRefreshToken($dataRefreshToken['hashedToken'], $expirationDate, $token->accessToken->id);
        return ['access_token' => $token->plainTextToken, 'refresh_token' => $dataRefreshToken['token']];
    }
}
