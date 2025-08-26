<?php

namespace App\Repositories\UserRepositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use PhpParser\Node\Expr\Cast\Double;

interface UserRepositoryInterface
{
    public function isExistUserById(int $userId): bool;
    public function isExistPasswordAccount(string $email): bool;

    public function getAllUsers(): Collection;

    public function getDateOfEndVipStatusByIdUser(int $idUser);

    public function getInfoUserAccountByEmail(string $email);

    public function getInfoUserAccountByProviderAndProviderId(string $providerId, string $provider);

    public function getInfoUserById(int $userId);

    public function updateNameAndAvatar(int $userId, string $name, string $avatar);

    public function updateCurrencyId(User $user, ?int $currencyId);

    public function updateTimezoneId(User $user, ?int $timezoneId);

    public function updateLanguageId(User $user, ?int $languageId);

    public function updateCoordinates(User $user, ?float $latitude, ?float $longitude);

    public function updateCurrencyIdByIdUser(int $userId, ?int $currencyId);

    public function updateTimezoneIdByIdUser(int $userId, ?int $timezoneId);

    public function updateLanguageIdByIdUser(int $userId, ?int $languageId);

    public function updateCoordinatesByIdUser(int $userId, ?float $latitude, ?float $longitude);

    public function getInfoAboutUsersForHistoryPurchaseSeeder();

    public function updateEndDateOfVipStatusByIdUser(int $idUser, string $endDate): bool;

    public function setInviter(int $userId, int $inviter_id);

    public function hasUserActivePremiumStatusByIdUser(int $idUser): bool;

    public function hasUserInviteCode(int $userId): bool;

    public function getAncestorsInviterOfUser($userId);

    public function getAllUsersWithConfirmedEmailForMailingNews();

    public function getUsersNearBy(float $latitude,float $longitude, int $radius);

    public function changeMyVisibility(User $user);
}
