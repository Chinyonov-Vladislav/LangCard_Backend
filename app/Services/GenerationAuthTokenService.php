<?php

namespace App\Services;

use App\Repositories\AuthTokenRepositories\AuthTokenRepositoryInterface;
use Carbon\Carbon;
use Str;

class GenerationAuthTokenService
{
    protected AuthTokenRepositoryInterface $refreshTokenRepository;

    public function __construct()
    {
        $this->refreshTokenRepository = app(AuthTokenRepositoryInterface::class);
    }

    public function generateRefreshToken(): array
    {
        do
        {
            $plainTextToken = Str::random(64); // исходный токен
            $hashedToken = $this->hashToken($plainTextToken);
        }
        while($this->refreshTokenRepository->isExistRefreshToken($hashedToken));
        return ['token'=>$plainTextToken, 'hashedToken'=>$hashedToken];
    }

    public function hashToken(string $plainTextToken): string
    {
        return hash_hmac('sha256', $plainTextToken, config('sanctum.jwt_secret')); // хешируем токен
    }
}
