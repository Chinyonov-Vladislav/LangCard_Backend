<?php

namespace App\Repositories\UserRepositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function getInfoUserAccountByEmail(string $email): ?User
    {
        return $this->model->where('email','=', $email)
            ->select(['id', 'name', 'email', 'type_user', 'currency_id', 'timezone_id','vip_status_time_end'])->first();
    }

    public function updateCurrencyId(User $user, ?int $currencyId): void
    {
        $user->currency_id = $currencyId;
        $user->save();
    }

    public function updateTimezoneId(User $user, ?int $timezoneId): void
    {
        $user->timezone_id = $timezoneId;
        $user->save();
    }

    public function updateCurrencyIdByIdUser(int $userId, ?int $currencyId)
    {
        $this->model->where('id', $userId)->update([
            'currency_id' => $currencyId
        ]);
    }

    public function updateTimezoneIdByIdUser(int $userId, ?int $timezoneId)
    {
        $this->model->where('id', $userId)->update([
            'timezone_id' => $timezoneId
        ]);
    }

    public function isExistPasswordAccount(string $email): bool
    {
        $user = $this->model->where('email', $email)->select(['password'])->first();
        return $user->password !== null;
    }

    public function getDateOfEndVipStatusByIdUser(int $idUser)
    {
        return $this->model->where('id', '=', $idUser)->first()->vip_status_time_end;
    }

    public function getInfoAboutUsersForHistoryPurchaseSeeder()
    {
        return $this->model->select(['id', 'currency_id', 'vip_status_time_end'])->get();
    }

    public function updateEndDateOfVipStatusByIdUser(int $idUser, string $endDate): bool
    {
        return $this->model
                ->where('id', '=', $idUser)
                ->update(['vip_status_time_end' => $endDate]) > 0;
    }

    public function isExistUserById(int $userId): bool
    {
        return $this->model->where('id', '=', $userId)->exists();
    }

    public function hasUserActivePremiumStatusByIdUser(int $idUser): bool
    {
        $currentUser = $this->model->select(['vip_status_time_end'])->where('id','=',$idUser)->first();
        if($currentUser->vip_status_time_end === null){
            return false;
        }
        $dateEndOfVipStatusOfCurrentUser = Carbon::parse($currentUser->vip_status_time_end);
        if($dateEndOfVipStatusOfCurrentUser->isPast())
        {
            return false;
        }
        return true;
    }

    public function getInfoUserById(int $userId)
    {
        return $this->model->with(['currency', 'timezone', 'inviter'])->where('id','=', $userId)
            ->select(['id', 'name', 'email', 'type_user','invite_code', 'currency_id', 'timezone_id','inviter_id','vip_status_time_end', 'created_at'])->first();
    }

    public function getInfoUserAccountByProviderAndProviderId(string $providerId, string $provider)
    {
        return $this->model->where('provider_id','=', $providerId)->where('provider','=', $provider)->first();
    }

    public function hasUserInviteCode(int $userId): bool
    {
        return $this->model->where('id', '=', $userId)->first()->invite_code !== null;
    }

    public function setInviter(int $userId, int $inviter_id): void
    {
        $this->model->where('id', '=', $userId)->update(['inviter_id'=>$inviter_id]);
    }

    public function getAncestorsInviterOfUser($userId)
    {
        return User::find($userId)->ancestors;
    }

    public function getAllUsers(): Collection
    {
        return $this->model->all();
    }
}
