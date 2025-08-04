<?php

namespace App\Services\GenerationCodeServices;

use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use Str;

class GenerationTwoFactorAuthorizationToken
{
    protected TwoFactorAuthorizationRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = app(TwoFactorAuthorizationRepositoryInterface::class);
    }

    public function generateTwoFactorAuthorizationToken(): array
    {
        do
        {
            $plainTextToken = Str::random(64); // исходный токен
            $hashedToken = $this->hashToken($plainTextToken);
        }
        while($this->repository->isExistTwoFactorToken($hashedToken));
        return ['token'=>$plainTextToken, 'hashedToken'=>$hashedToken];
    }

    public function hashToken(string $plainTextToken): string
    {
        return hash_hmac('sha256', $plainTextToken, config('sanctum.jwt_secret')); // хешируем токен
    }
}
