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

    /**
     * @OA\Get(
     *     path="/dailyRewards",
     *     summary="Получить данные о ежедневных наградах авторизованного пользователя",
     *     description="Возвращает список ежедневных наград и информацию о том, может ли текущий пользователь забрать награду сегодня.",
     *     tags={"Ежедневная награда"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Данные получены успешно",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о ежедневных наградах для авторизованного пользователя"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/DailyRewardResource")
     *                 ),
     *                 @OA\Property(
     *                     property="can_user_take_daily_reward",
     *                     type="boolean",
     *                     example=true,
     *                     description="Флаг, указывающий, может ли пользователь забрать награду сегодня"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */

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

    /**
     * @OA\Post(
     *     path="/api/v1/dailyRewards",
     *     summary="Забрать ежедневную награду",
     *     description="Позволяет авторизованному пользователю получить награду за текущий день, если она доступна.",
     *     tags={"Ежедневная награда"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Награда успешно получена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пользователь успешно забрал ежедневную награду"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Конфликт — награда уже забрана сегодня или отсутствует для этого дня",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Пользователь не может забрать ежедневную награду, так как сегодня он уже её забирал"
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function takeDailyReward()
    {
        $dataDailyRewardInfoOfUser = $this->dailyRewardRepository->getDailyRewardInfoOfUser(auth()->id());
        if ($dataDailyRewardInfoOfUser->last_date_daily_reward === null) {
            $numberDayStreak = 0;
        } else {
            $now = Carbon::now();
            $lastDateTakenDailyReward = Carbon::parse($dataDailyRewardInfoOfUser->last_date_daily_reward);
            $differenceInDays = (int)$now->diffInDays($lastDateTakenDailyReward, true);
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
        $infoDailyRewardForCurrentDay = $this->dailyRewardRepository->getRewardForDay($numberDay);
        if($infoDailyRewardForCurrentDay === null)
        {
            return ApiResponse::error("Отсутствует награда для дня с номером = $numberDay", null, 409);
        }
        $newCountPoints = $infoDailyRewardForCurrentDay->reward + $dataDailyRewardInfoOfUser->point_count;
        $newDayStreak = $numberDayStreak+1;
        $newDate = Carbon::now();
        $this->dailyRewardRepository->setDataAboutDailyRewardAfterUserTakeDailyReward(auth()->id(), $newCountPoints, $newDayStreak, $newDate);
        return ApiResponse::success('Пользователь успешно забрал ежедневную награду');
    }
}
