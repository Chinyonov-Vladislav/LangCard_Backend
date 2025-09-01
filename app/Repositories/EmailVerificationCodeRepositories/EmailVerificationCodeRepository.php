<?php

namespace App\Repositories\EmailVerificationCodeRepositories;

use App\Models\User;
use Carbon\Carbon;

class EmailVerificationCodeRepository implements EmailVerificationCodeRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function saveVerificationCode(string $code, Carbon $expirationCodeDate, int $userId): void
    {
        $this->model->where('id','=', $userId)->update([
            'email_verification_code' => $code,
            'email_verification_code_expiration_date'=>$expirationCodeDate
        ]);
    }

    public function isExistCode(string $code): bool
    {
        return $this->model->where('email_verification_code', '=', $code)->exists();
    }

    public function getInfoCodeByUserId(int $userId)
    {
        return $this->model->where('id','=', $userId)->select(['email_verification_code', 'email_verification_code_expiration_date','email_verified_at'])->first();
    }

    public function verificateEmailAddress(int $userId): void
    {
        $this->model->where('id','=', $userId)->update(['email_verification_code'=>null,
            'email_verification_code_expiration_date'=>null, 'email_verified_at'=>Carbon::now() ]);
    }

    public function verificateEmailAddressForUser(User $user): User
    {
        $user->email_verification_code=null;
        $user->email_verification_code_expiration_date = null;
        $user->email_verified_at = Carbon::now();
        $user->save();
        return $user;
    }
}
