<?php

namespace Database\Seeders;

use App\Repositories\DailyRewardRepositories\DailyRewardRepositoryInterface;
use Illuminate\Database\Seeder;

class DailyRewardSeeder extends Seeder
{
    protected DailyRewardRepositoryInterface $dailyRewardRepository;

    public function __construct(DailyRewardRepositoryInterface $dailyRewardRepository)
    {
        $this->dailyRewardRepository = $dailyRewardRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['reward' => 5],
            ['reward' => 10],
            ['reward' => 15],
            ['reward' => 20],
            ['reward' => 25],
            ['reward' => 30],
            ['reward' => 35],
            ['reward' => 40],
            ['reward' => 50],
            ['reward' => 60],
            ['reward' => 70],
            ['reward' => 80],
            ['reward' => 90],
            ['reward' => 100],
            ['reward' => 115],
            ['reward' => 130],
            ['reward' => 145],
            ['reward' => 160],
            ['reward' => 180],
            ['reward' => 200],
            ['reward' => 225],
            ['reward' => 250],
            ['reward' => 275],
            ['reward' => 300],
            ['reward' => 330],
            ['reward' => 360],
            ['reward' => 390],
            ['reward' => 420],
            ['reward' => 450],
            ['reward' => 500],
        ];
        $numberDay = 1;
        foreach ($data as $item) {
            if(!$this->dailyRewardRepository->isExistDailyRewardByNumberDay($numberDay))
            {
                $this->dailyRewardRepository->saveDailyReward($numberDay,$item['reward']);
            }
            $numberDay++;
        }
    }
}
