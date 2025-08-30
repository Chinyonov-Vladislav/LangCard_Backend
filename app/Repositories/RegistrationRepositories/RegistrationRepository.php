<?php

namespace App\Repositories\RegistrationRepositories;

use App\Models\User;
use Carbon\Carbon;

class RegistrationRepository implements RegistrationRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function registerUser(string $name,
                                 ?string $email,
                                 ?string $password,
                                 string $type_user = 'user',
                                 ?string $avatar_url = null,
                                 ?string $vip_status_time_end = null,
                                 ?int $timezone_id = null,
                                 ?int $currency_id = null,
                                 ?int $language_id = null,
                                 ?float $latitude = null,
                                 ?float $longitude = null,
                                 ?string $providerId = null,
                                 ?string $providerName = null,
                                 bool $mailing_enabled = false ): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = bcrypt($password);
        if($timezone_id !== null){
            $user->timezone_id = $timezone_id;
            $user->last_time_update_timezone = Carbon::now();
        }
        if($currency_id !== null){
            $user->currency_id = $currency_id;
            $user->last_time_update_currency = Carbon::now();
        }
        if($language_id !== null){
            $user->language_id = $language_id;
            $user->last_time_update_language = Carbon::now();
        }

        if($latitude !== null && $longitude !== null){
            $user->latitude = $latitude;
            $user->longitude = $longitude;
            $user->last_time_update_coordinates = Carbon::now();
        }
        $user->avatar_url = $avatar_url;
        $user->type_user = $type_user;
        $user->vip_status_time_end = $vip_status_time_end;
        $user->provider_id = $providerId;
        $user->provider = $providerName;
        $user->mailing_enabled = $mailing_enabled;
        $user->save();
        return $user;
    }

    public function isExistUserByEmail($email): bool
    {
        return $this->model->where('email','=', $email)->exists();
    }
}
