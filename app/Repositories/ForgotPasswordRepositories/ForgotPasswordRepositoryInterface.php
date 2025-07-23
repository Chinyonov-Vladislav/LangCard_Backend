<?php

namespace App\Repositories\ForgotPasswordRepositories;

interface ForgotPasswordRepositoryInterface
{

    public function getInfoAboutTokenResetPassword($email);
    public function updateOrCreateTokenByEmail($email, $token);

    public function updatePassword($email, $password);

    public function deleteTokenByEmail($email);
}
