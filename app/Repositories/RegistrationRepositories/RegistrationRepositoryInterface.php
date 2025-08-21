<?php

namespace App\Repositories\RegistrationRepositories;

use App\Models\User;

interface RegistrationRepositoryInterface
{
    public function isExistUserByEmail($email);
    public function registerUser(string $name,
                                 ?string $email,
                                 ?string $password,
                                 ?int $timezone_id,
                                 ?int $currency_id,
                                 ?string $avatar_url = null,
                                 string $type_user = 'user',
                                 ?string $vip_status_time_end = null,
                                 ?string $providerId = null,
                                 ?string $providerName = null,
                                 bool $mailing_enabled = false,
    ): User;
}
