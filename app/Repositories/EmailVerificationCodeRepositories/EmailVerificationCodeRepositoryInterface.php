<?php

namespace App\Repositories\EmailVerificationCodeRepositories;

use Carbon\Carbon;

interface EmailVerificationCodeRepositoryInterface
{
    public function getInfoCodeByUserId(int $userId);

    public function verificateEmailAddress(int $userId);
    public function isExistCode(string $code): bool;
    public function saveVerificationCode(string $code,Carbon $expirationCodeDate, int $userId);
}
