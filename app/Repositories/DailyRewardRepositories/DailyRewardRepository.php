<?php

namespace App\Repositories\DailyRewardRepositories;

use App\Models\DailyReward;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DailyRewardRepository implements DailyRewardRepositoryInterface
{
    protected DailyReward $model;

    public function __construct(DailyReward $model)
    {
        $this->model = $model;
    }

    public function isExistDailyRewardByNumberDay(int $numberDay): bool
    {
        return $this->model->where('number_date','=',$numberDay)->exists();
    }

    public function saveDailyReward(int $numberDay, int $reward): DailyReward
    {
        $newDailyReward = new DailyReward();
        $newDailyReward->number_date = $numberDay;
        $newDailyReward->reward = $reward;
        $newDailyReward->save();
        return $newDailyReward;
    }

    public function updateRewardForDay(int $numberDay, int $reward): void
    {
        $this->model->where('number_date','=',$numberDay)->update(['reward' => $reward]);
    }

    public function getDailyRewardInfo(): Collection
    {
        return $this->model->all();
    }

    public function getDailyRewardInfoOfUser(int $userId)
    {
        return User::where('id','=', $userId)->select(['daily_reward_streak', 'last_date_daily_reward', 'point_count'])->first();
    }

    public function setDailyRewardStreakForUser(int $userId, int $countStreak): void
    {
        User::where('id','=', $userId)->update(['daily_reward_streak'=>$countStreak]);
    }

    public function getRewardForDay(int $numberDay)
    {
        return $this->model->where('number_date', '=', $numberDay)->first();
    }

    public function getCountDaysExistReward()
    {
        return $this->model->max('number_date');
    }

    public function setDataAboutDailyRewardAfterUserTakeDailyReward(int $userId, int $newCountPoints, int $newDailyStreak, Carbon $lastDailyReward): void
    {
        User::where('id','=', $userId)->update(['point_count'=>$newCountPoints,'last_date_daily_reward'=>$lastDailyReward,'daily_reward_streak'=>$newDailyStreak]);
    }

}
