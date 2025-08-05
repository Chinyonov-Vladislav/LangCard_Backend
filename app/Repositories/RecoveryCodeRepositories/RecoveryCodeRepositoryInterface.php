<?php

namespace App\Repositories\RecoveryCodeRepositories;

use App\Models\RecoveryCode;

interface RecoveryCodeRepositoryInterface
{
    public function getRecoveryCodeForUser(int $userId, string $hashedRecoveryCode);

    public function deleteRecoveryCode(int $userId, string $code);
    public function deleteRecoveryCodesForUser(int $userId);
    public function saveRecoveryCode(int $userId, string $recoveryCode);
}
