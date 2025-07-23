<?php

namespace App\Repositories\ForgotPasswordRepositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotForgotPasswordRepository implements ForgotPasswordRepositoryInterface
{

    public function updateOrCreateTokenByEmail($email, $token): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token),
                'created_at' => Carbon::now()]
        );
    }

    public function deleteTokenByEmail($email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    public function getInfoAboutTokenResetPassword($email)
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();
    }

    public function updatePassword($email, $password): void
    {
        DB::table('users')->where('email', $email)->update(['password' => Hash::make($password)]);
    }
}
