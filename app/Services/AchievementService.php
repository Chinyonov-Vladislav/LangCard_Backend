<?php

namespace App\Services;

use App\Repositories\AchievementRepositories\AchievementRepositoryInterface;
use App\Repositories\UserAchievementRepositories\UserAchievementRepositoryInterface;

class AchievementService
{
    protected AchievementRepositoryInterface $achievementRepository;
    protected UserAchievementRepositoryInterface $userAchievementRepository;

    public function __construct()
    {
        $this->achievementRepository = app(AchievementRepositoryInterface::class);
        $this->userAchievementRepository = app(UserAchievementRepositoryInterface::class);
    }

    public function startAchievementsForNewUser(int $newUserId): void
    {
        if (!$this->userAchievementRepository->hasUserAchievements($newUserId)) {
            $achievements = $this->achievementRepository->getAllAchievements();
            foreach ($achievements as $achievement) {
                $this->userAchievementRepository->saveNewUserAchievement($newUserId, $achievement->id);
            }
        }
    }

    public function addProgress(int $userId,int $achievementId, int $amount = 1): void
    {
        $progressAchievementOfUser = $this->userAchievementRepository->getProgressOfAchievementOfUser($achievementId, $userId);
        $targetProgress = $progressAchievementOfUser->achievement->target;
        if($progressAchievementOfUser->progress >= $targetProgress) {
            return;
        }
        $newProgress = $progressAchievementOfUser->progress + $amount;
        $this->userAchievementRepository->updateProgressOfAchievement($achievementId, $userId, $newProgress);
        if($newProgress >= $targetProgress)
        {
            $this->userAchievementRepository->updateDateUnlockedAchievement($achievementId, $userId);
            // TODO добавить уведомление о том, что пользователь открыл достижение
        }
    }
}
