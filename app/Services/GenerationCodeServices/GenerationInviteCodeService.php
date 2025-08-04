<?php

namespace App\Services\GenerationCodeServices;

use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;

class GenerationInviteCodeService
{
    private const LENGTH_INVITE_CODE = 16;
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';


    protected InviteCodeRepositoryInterface $inviteCodeRepository;

    public function __construct()
    {
        $this->inviteCodeRepository = app(InviteCodeRepositoryInterface::class);
    }

    public function generateInviteCode(): string
    {
        do
        {
            $code =  substr(str_shuffle(str_repeat(self::ALPHABET, ceil(self::LENGTH_INVITE_CODE / strlen(self::ALPHABET)))), 0, self::LENGTH_INVITE_CODE);
        }
        while ($this->inviteCodeRepository->isExistUserWithInviteCode($code));
        return $code;
    }
}
