<?php

namespace App\Repositories\TwoFactorAuthorizationRepositories;

use Carbon\Carbon;

interface TwoFactorAuthorizationRepositoryInterface
{

    public function isExistTwoFactorToken(string $token);

    public function getTokenWithUser(string $hashedToken);

    public function deleteToken(string $hashedToken);

    public function updateOrSaveTwoFactorAuthorizationCode(string $code, int $user_id);

    public function switchTwoFactorAuthenticationEmail(int $userId);

    public function updateDataTwoFactorAuthenticationEmail(int $userId,string $code, Carbon $expirationDate );
}
