<?php

namespace App\Repositories\UserProviderRepositories;

use App\Models\UserProvider;

class UserProviderRepository implements UserProviderRepositoryInterface
{

    protected UserProvider $model;
    public function __construct(UserProvider $model)
    {
        $this->model = $model;
    }


    public function getDataProviderWithUser(string $providerId, string $provider): ?UserProvider
    {
        return $this->model->with(['user'])->where('provider_id', $providerId)->where('provider', $provider)->first();
    }

    public function saveUserProvider(int $userId, string $providerId, string $providerName): UserProvider
    {
        $newUserProvider = new UserProvider();
        $newUserProvider->provider_id = $providerId;
        $newUserProvider->provider = $providerName;
        $newUserProvider->user_id = $userId;
        $newUserProvider->save();
        return $newUserProvider;
    }
}
