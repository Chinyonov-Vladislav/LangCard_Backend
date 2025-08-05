<?php

namespace App\Services\GenerationCodeServices;

use App\Repositories\RecoveryCodeRepositories\RecoveryCodeRepositoryInterface;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepository;
use App\Repositories\TwoFactorAuthorizationRepositories\TwoFactorAuthorizationRepositoryInterface;
use Str;

class GenerationRecoveryCodeService
{
    protected RecoveryCodeRepositoryInterface $recoveryCodeRepository;
    protected ?int $userId = null;
    private const LENGTH_RECOVERY_CODE = 8;
    public function __construct()
    {
        $this->recoveryCodeRepository = app(RecoveryCodeRepositoryInterface::class);
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function generateRecoveryCode(): ?array
    {
        if($this->userId === null){
            return null;
        }
        do
        {
            $recoveryCode = Str::random(self::LENGTH_RECOVERY_CODE);
            $hashedRecoveryCode = $this->hashRecoveryCode($recoveryCode);
        }
        while($this->recoveryCodeRepository->getRecoveryCodeForUser($this->userId, $hashedRecoveryCode) !== null);
        return ['recoveryCode'=>$recoveryCode, 'hashedRecoveryCode'=>$hashedRecoveryCode];
    }

    public function hashRecoveryCode(string $recoveryCode): string
    {
        return hash_hmac('sha256', $recoveryCode, config('sanctum.jwt_secret')); // хешируем токен
    }
}
