<?php

namespace App\Repositories\LoginRepositories;

use App\Models\User;

interface LoginRepositoryInterface
{
    public function getUserByEmail($email);

}
