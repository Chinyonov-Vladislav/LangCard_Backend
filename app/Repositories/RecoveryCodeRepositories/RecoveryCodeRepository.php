<?php

namespace App\Repositories\RecoveryCodeRepositories;

use App\Models\RecoveryCode;

class RecoveryCodeRepository implements RecoveryCodeRepositoryInterface
{
    protected RecoveryCode $model;
    public function __construct(RecoveryCode $model)
    {
        $this->model = $model;
    }

    public function deleteRecoveryCodesForUser(int $userId): void
    {
        $this->model->where('user_id','=', $userId)->delete();
    }

    public function saveRecoveryCode(int $userId, string $recoveryCode): RecoveryCode
    {
        $newRecoveryCode = new RecoveryCode();
        $newRecoveryCode->code = $recoveryCode;
        $newRecoveryCode->user_id = $userId;
        $newRecoveryCode->save();
        return $newRecoveryCode;
    }

    public function deleteRecoveryCode(int $userId, string $code): void
    {
        $this->model->where('user_id','=', $userId)->where('code','=', $code)->delete();
    }

    public function getRecoveryCodeForUser(int $userId, string $hashedRecoveryCode): ?RecoveryCode
    {
        return $this->model->where('user_id', '=', $userId)->where('code', '=', $hashedRecoveryCode)->first();
    }

    public function getCountActiveRecoveryCodeForUser(int $userId): int
    {
        return $this->model->where('user_id', '=', $userId)->count();
    }
}
