<?php

namespace App\Repositories\RegistrationRepositories;

use App\Models\User;

class RegistrationRepository implements RegistrationRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function registerUser(string $name,
                                 string $email,
                                 ?string $password,
                                 ?int $timezone_id,
                                 ?int $currency_id,
                                 ?string $avatar_url = null,
                                 string $type_user = 'user',
                                 ?string $vip_status_time_end = null): void
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->timezone_id = $timezone_id;
        $user->currency_id = $currency_id;
        $user->avatar_url = $avatar_url;
        $user->type_user = $type_user;
        $user->vip_status_time_end = $vip_status_time_end;
        $user->save();
    }

    public function isExistUserByEmail($email)
    {
        return $this->model->where('email','=', $email)->exists();
    }
}
