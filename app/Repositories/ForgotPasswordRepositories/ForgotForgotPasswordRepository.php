<?php

namespace App\Repositories\ForgotPasswordRepositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotForgotPasswordRepository implements ForgotPasswordRepositoryInterface
{

    public function updateOrCreateTokenByEmail(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );
    }

    public function getInfoAboutTokenResetPassword(string $token)
    {
        return DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();
    }

    public function updatePassword($email, $password): void
    {
        DB::table('users')->where('email', $email)->update(['password' => Hash::make($password)]);
    }

    public function deleteToken(string $token): void
    {
        DB::table('password_reset_tokens')->where('token','=', $token)->delete();
    }
}
