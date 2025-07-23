<?php

namespace App\Repositories\LoginRepositories;

use App\Models\User;

class LoginRepository implements LoginRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function getUserByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }
}
