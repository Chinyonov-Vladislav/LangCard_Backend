<?php

namespace App\Services;

use App\Repositories\EmailVerificationCodeRepositories\EmailVerificationCodeRepositoryInterface;
use Random\RandomException;

class GenerationCodeVerificationEmail
{
    protected EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository;
    public function __construct(EmailVerificationCodeRepositoryInterface $emailVerificationCodeRepository)
    {
        $this->emailVerificationCodeRepository = $emailVerificationCodeRepository;
    }

    /**
     * @throws RandomException
     */
    public function generateCode(): string
    {
        do{
            $code = (string)random_int(100000, 999999);
        }
        while($this->emailVerificationCodeRepository->isExistCode($code));
        return $code;
    }
}
