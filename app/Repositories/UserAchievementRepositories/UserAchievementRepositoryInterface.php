<?php

namespace App\Repositories\UserAchievementRepositories;

use App\Models\UserAchievement;

interface UserAchievementRepositoryInterface
{
    public function hasUserAchievements(int $userId): bool;

    public function getProgressOfAchievementOfUser(int $achievementId, int $userId):?UserAchievement;

    public function updateProgressOfAchievement(int $achievementId, int $userId, int $progress);

    public function updateDateUnlockedAchievement(int $achievementId, int $userId);

    public function saveNewUserAchievement(int $userId, int $achievementId):UserAchievement;
}
