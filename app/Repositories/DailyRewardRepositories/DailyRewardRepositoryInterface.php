<?php

namespace App\Repositories\DailyRewardRepositories;

use Carbon\Carbon;

interface DailyRewardRepositoryInterface
{
    public function isExistDailyRewardByNumberDay(int $numberDay): bool;

    public function getDailyRewardInfo();

    public function getDailyRewardInfoOfUser(int $userId);
    public function saveDailyReward(int $numberDay, int $reward);

    public function updateRewardForDay(int $numberDay, int $reward);

    public function setDailyRewardStreakForUser(int $userId, int $countStreak);

    public function setDataAboutDailyRewardAfterUserTakeDailyReward(int $userId, int $newCountPoints, int $newDailyStreak, Carbon $lastDailyReward);

    public function getRewardForDay(int $numberDay);

    public function getCountDaysExistReward();

}
