<?php

namespace App\Repositories\LoginRepositories;

interface LoginRepositoryInterface
{
    public function getUserByEmail($email);

}
