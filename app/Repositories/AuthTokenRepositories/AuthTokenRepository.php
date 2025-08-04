<?php

namespace App\Repositories\AuthTokenRepositories;

use App\Models\RefreshToken;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTokenRepository implements AuthTokenRepositoryInterface
{
    protected RefreshToken $refreshTokenModel;
    protected PersonalAccessToken $personalAccessTokenModel;

    public function __construct(RefreshToken $model, PersonalAccessToken $personalAccessTokenModel)
    {
        $this->refreshTokenModel = $model;
        $this->personalAccessTokenModel = $personalAccessTokenModel;
    }

    public function getRefreshToken(string $hashedToken): ?RefreshToken
    {
        return $this->refreshTokenModel->with(['personalAccessToken'=>function ($query) {
            $query->with('tokenable');
        }])->where('token', '=', $hashedToken)->first();
    }

    public function saveRefreshToken(string $hashedToken, Carbon $expiresAt,int $tokenId): void
    {
        $newRefreshToken = new RefreshToken();
        $newRefreshToken->token = $hashedToken;
        $newRefreshToken->expires_at = $expiresAt;
        $newRefreshToken->personal_access_token_id = $tokenId;
        $newRefreshToken->save();
    }

    public function isExistRefreshToken(string $hashedToken)
    {
        return $this->refreshTokenModel->where('token', '=', $hashedToken)->exists();
    }

    public function deleteRefreshTokenForUserByIdAccessToken(int $accessTokenId): void
    {
        $this->refreshTokenModel->where('personal_access_token_id', '=', $accessTokenId)->delete();
    }

    public function deleteAccessTokenById(int $tokenId): void
    {
        $this->personalAccessTokenModel->where('id','=',$tokenId)->delete();
    }
}
