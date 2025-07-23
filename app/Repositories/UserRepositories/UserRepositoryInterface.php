<?php

namespace App\Repositories\UserRepositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function isExistUserById(int $userId): bool;
    public function isExistPasswordAccount(string $email): bool;

    public function getDateOfEndVipStatusByIdUser(int $idUser);

    public function getInfoUserAccountByEmail(string $email);

    public function getInfoUserById(int $userId);

    public function updateCurrencyId(User $user, ?int $currencyId);

    public function updateTimezoneId(User $user, ?int $timezoneId);

    public function updateCurrencyIdByIdUser(int $userId, ?int $currencyId);

    public function updateTimezoneIdByIdUser(int $userId, ?int $timezoneId);

    public function getInfoAboutUsersForHistoryPurchaseSeeder();

    public function updateEndDateOfVipStatusByIdUser(int $idUser, string $endDate): bool;

    public function hasUserActivePremiumStatusByIdUser(int $idUser): bool;
}
