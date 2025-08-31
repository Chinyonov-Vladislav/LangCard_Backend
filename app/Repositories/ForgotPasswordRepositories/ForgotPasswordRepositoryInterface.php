<?php

namespace App\Repositories\ForgotPasswordRepositories;

interface ForgotPasswordRepositoryInterface
{

    public function getInfoAboutTokenResetPassword(string $token);
    public function updateOrCreateTokenByEmail(string $email, string $token);

    public function updatePassword($email, $password);

    public function deleteToken(string $token);
}
