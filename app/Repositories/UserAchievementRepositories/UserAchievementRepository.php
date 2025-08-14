<?php

namespace App\Repositories\UserAchievementRepositories;

use App\Models\UserAchievement;

class UserAchievementRepository implements UserAchievementRepositoryInterface
{

    protected UserAchievement $model;
    public function __construct(UserAchievement $model)
    {
        $this->model = $model;
    }

    public function saveNewUserAchievement(int $userId, int $achievementId): UserAchievement
    {
        $newUserAchievement = new UserAchievement();
        $newUserAchievement->user_id = $userId;
        $newUserAchievement->achievement_id = $achievementId;
        $newUserAchievement->save();
        return $newUserAchievement;
    }

    public function hasUserAchievements(int $userId): bool
    {
        return $this->model->where("user_id", '=', $userId)->count() > 0;
    }


    public function updateProgressOfAchievement(int $achievementId, int $userId, int $progress): void
    {
        $this->model->where("achievement_id","=",$achievementId)->where("user_id", "=", $userId)->update(['progress'=>$progress]);
    }

    public function getProgressOfAchievementOfUser(int $achievementId, int $userId): ?UserAchievement
    {
        return $this->model->with(['achievement'])->where("achievement_id","=",$achievementId)->where("user_id", "=", $userId)->first();
    }

    public function updateDateUnlockedAchievement(int $achievementId, int $userId)
    {
        $this->model->where("achievement_id","=",$achievementId)->where("user_id", "=", $userId)->update(['unlocked_at'=>now()]);
    }
}
