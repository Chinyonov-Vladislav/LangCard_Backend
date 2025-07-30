<?php

namespace App\Services;

use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepositoryInterface;

class GenerationCodeVerificationEmail
{
    protected EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository;
    public function __construct(EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository)
    {
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
    }
    public function generateCode()
    {
        do{
            $code = (string)random_int(100000, 999999);
        }
        while($this->emailVerificationCodeRepository->isExistCode($code));
        return $code;
    }
}
