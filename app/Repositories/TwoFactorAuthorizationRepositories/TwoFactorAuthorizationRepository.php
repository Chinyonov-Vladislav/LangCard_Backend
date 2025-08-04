<?php

namespace App\Repositories\TwoFactorAuthorizationRepositories;

use App\Models\Card;
use App\Models\TwoFactorAuthorizationToken;
use App\Models\User;
use Carbon\Carbon;

class TwoFactorAuthorizationRepository implements TwoFactorAuthorizationRepositoryInterface
{
    protected User $model;

    protected TwoFactorAuthorizationToken $twoFactorAuthorizationTokenModel;

    public function __construct(User $model, TwoFactorAuthorizationToken $twoFactorAuthorizationTokenModel)
    {
        $this->model = $model;
        $this->twoFactorAuthorizationTokenModel = $twoFactorAuthorizationTokenModel;
    }

    public function switchTwoFactorAuthenticationEmail(int $userId)
    {
        $user = User::find($userId);
        $user->two_factor_email_enabled = !$user->two_factor_email_enabled;
        $user->two_factor_code_email = null;
        $user->two_factor_code_email_expiration_date = null;
        $user->save();
        return $user;
    }

    public function updateDataTwoFactorAuthenticationEmail(int $userId,string $code, Carbon $expirationDate): void
    {
        $user = User::find($userId);
        $user->two_factor_code_email = $code;
        $user->two_factor_code_email_expiration_date = $expirationDate;
        $user->save();
    }

    public function updateOrSaveTwoFactorAuthorizationCode(string $code, int $user_id): void
    {
        $this->twoFactorAuthorizationTokenModel->updateOrCreate(
            ['user_id' => $user_id],
            ['token' => $code]
        );
    }

    public function isExistTwoFactorToken(string $token): bool
    {
        return $this->twoFactorAuthorizationTokenModel->where('token', '=', $token)->exists();
    }

    public function getTokenWithUser(string $hashedToken): ?TwoFactorAuthorizationToken
    {
        return $this->twoFactorAuthorizationTokenModel->with('user')->where('token', '=', $hashedToken)->first();
    }

    public function deleteToken(string $hashedToken): void
    {
        $this->twoFactorAuthorizationTokenModel->where('token', '=', $hashedToken)->delete();
    }
}
