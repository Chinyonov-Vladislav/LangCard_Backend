<?php

namespace App\Repositories\RegistrationRepositories;

use App\Models\User;

interface RegistrationRepositoryInterface
{
    public function isExistUserByEmail($email);
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
                                 bool $mailing_enabled = false,
    ): User;
}
