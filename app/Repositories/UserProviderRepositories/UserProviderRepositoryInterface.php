<?php

namespace App\Repositories\UserProviderRepositories;

use App\Models\UserProvider;

interface UserProviderRepositoryInterface
{
    public function getDataProviderWithUser(string $providerId, string $provider): ?UserProvider;

    public function saveUserProvider(int $userId, string $providerId, string $providerName): UserProvider;
}
