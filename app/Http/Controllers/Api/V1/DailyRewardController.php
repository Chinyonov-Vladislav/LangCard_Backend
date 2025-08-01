<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\DailyRewardResources\DailyRewardResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\DailyRewardRepositories\DailyRewardRepositoryInterface;
use Carbon\Carbon;

class DailyRewardController extends Controller
{
    protected DailyRewardRepositoryInterface $dailyRewardRepository;

    public function __construct(DailyRewardRepositoryInterface $dailyRewardRepository)
    {
        $this->dailyRewardRepository = $dailyRewardRepository;
    }

    public function getDailyRewardsForAuthUser()
    {
        $dataDailyRewardInfoOfUser = $this->dailyRewardRepository->getDailyRewardInfoOfUser(auth()->id());
        $data = $this->dailyRewardRepository->getDailyRewardInfo();
        $canUserTakeDailyReward = true;
        if ($dataDailyRewardInfoOfUser->daily_reward_streak === null || $dataDailyRewardInfoOfUser->last_date_daily_reward == null) {
            $this->dailyRewardRepository->setDailyRewardStreakForUser(auth()->id(), 0);
            foreach ($data as $item) {
                $item->is_take = false;
            }
        } else {
            $now = Carbon::now();
            $lastDateTakenDailyReward = Carbon::parse($dataDailyRewardInfoOfUser->last_date_daily_reward);
            if ($now->diffInDays($lastDateTakenDailyReward) == 0) {
                $canUserTakeDailyReward = false;
            } elseif ($now->diffInDays($lastDateTakenDailyReward) != 1) {
                $this->dailyRewardRepository->setDailyRewardStreakForUser(auth()->id(), 0);
            }
            $numberDaysTaken = $dataDailyRewardInfoOfUser->daily_reward_streak % count($data);
            for ($i = 0; $i < count($data); $i++) {
                if ($i < $numberDaysTaken) {
                    $data[$i]->is_take = true;
                } else {
                    $data[$i]->is_take = false;
                }
            }
        }
        return ApiResponse::success('Данные о ежедневных наградах для авторизованного пользователя', (object)['items'=>DailyRewardResource::collection($data),
            'can_user_take_daily_reward' => $canUserTakeDailyReward]);
    }

    public function takeDailyReward()
    {
        $dataDailyRewardInfoOfUser = $this->dailyRewardRepository->getDailyRewardInfoOfUser(auth()->id());
        if ($dataDailyRewardInfoOfUser->last_date_daily_reward === null) {
            $numberDayStreak = 0;
        } else {
            $now = Carbon::now();
            $lastDateTakenDailyReward = Carbon::parse($dataDailyRewardInfoOfUser->last_date_daily_reward);
            $differenceInDays = (int)$now->diffInDays($lastDateTakenDailyReward, true);
            logger("Разница в количестве дней = $differenceInDays");
            if ($differenceInDays === 0) {
                return ApiResponse::error('Пользователь не может забрать ежедневную награду, так как сегодня он уже её забирал', null, 409);
            } elseif ($differenceInDays === 1) {
                $numberDayStreak = $dataDailyRewardInfoOfUser->daily_reward_streak;
            } else {
                $numberDayStreak = 0;
            }
        }
        $countDaysForRewardExist = $this->dailyRewardRepository->getCountDaysExistReward();
        $numberDay = ($numberDayStreak % $countDaysForRewardExist) + 1;
        logger("Текущий streak = $numberDayStreak");
        logger("Номер дня награды = $numberDay");
        $infoDailyRewardForCurrentDay = $this->dailyRewardRepository->getRewardForDay($numberDay);
        if($infoDailyRewardForCurrentDay === null)
        {
            return ApiResponse::error("Отсутствует награда для дня с номером = $numberDay", null, 409);
        }
        logger("награда = $infoDailyRewardForCurrentDay->reward");
        $newCountPoints = $infoDailyRewardForCurrentDay->reward + $dataDailyRewardInfoOfUser->point_count;
        $newDayStreak = $numberDayStreak+1;
        $newDate = Carbon::now();
        $this->dailyRewardRepository->setDataAboutDailyRewardAfterUserTakeDailyReward(auth()->id(), $newCountPoints, $newDayStreak, $newDate);
        return ApiResponse::success('Пользователь успешно забрал ежедневную награду');
    }
}
