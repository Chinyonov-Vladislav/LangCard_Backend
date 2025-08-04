<?php

namespace App\Repositories\AuthTokenRepositories;

use App\Models\RefreshToken;
use Carbon\Carbon;

interface AuthTokenRepositoryInterface
{
    public function getRefreshToken(string $hashedToken): ?RefreshToken;
    public function isExistRefreshToken(string $hashedToken);
    public function saveRefreshToken(string $hashedToken, Carbon $expiresAt, int $tokenId);
    public function deleteRefreshTokenForUserByIdAccessToken(int $accessTokenId);

    public function deleteAccessTokenById(int $tokenId);
}
