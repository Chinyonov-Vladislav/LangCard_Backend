<?php

namespace App\Repositories\InviteCodeRepositories;

use App\Models\User;

interface InviteCodeRepositoryInterface
{
    public function isExistUserWithInviteCode(string $inviteCode): bool;

    public function getUserWithInviteCode(string $inviteCode): ?User;

    public function saveInviteCode(int $userId, string $inviteCode);
}
