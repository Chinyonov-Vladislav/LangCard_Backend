<?php

namespace App\Repositories\InviteCodeRepositories;

use App\Models\User;

class InviteCodeRepository implements InviteCodeRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function isExistUserWithInviteCode(string $inviteCode): bool
    {
        return $this->model->where('invite_code','=', $inviteCode)->exists();
    }


    public function saveInviteCode(int $userId, string $inviteCode): void
    {
        $this->model->where('id', '=', $userId)->update(['invite_code' => $inviteCode]);
    }

    public function getUserWithInviteCode(string $inviteCode): ?User
    {
        return $this->model->where('invite_code','=', $inviteCode)->first();
    }
}
