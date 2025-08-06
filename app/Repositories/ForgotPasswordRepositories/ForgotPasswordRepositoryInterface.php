<?php

namespace App\Repositories\ForgotPasswordRepositories;

interface ForgotPasswordRepositoryInterface
{

    public function getInfoAboutTokenResetPassword($email);
    public function updateOrCreateTokenByEmail(string $email, string $token);

    public function updatePassword($email, $password);

    public function deleteTokenByEmail($email);
}
