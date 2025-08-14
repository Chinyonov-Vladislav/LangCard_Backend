<?php

namespace Database\Seeders;

use App\Repositories\AchievementRepositories\AchievementRepositoryInterface;
use App\Repositories\UserAchievementRepositories\UserAchievementRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserAchievementSeeder extends Seeder
{
    protected UserRepositoryInterface $userRepository;
    protected AchievementRepositoryInterface $achievementRepository;

    protected UserAchievementRepositoryInterface $userAchievementRepository;

    public function __construct(UserRepositoryInterface $userRepository,
                                AchievementRepositoryInterface $achievementRepository,
                                UserAchievementRepositoryInterface $userAchievementRepository)
    {
        $this->userRepository = $userRepository;
        $this->achievementRepository = $achievementRepository;
        $this->userAchievementRepository = $userAchievementRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->userRepository->getAllUsers();
        $achievements = $this->achievementRepository->getAllAchievements();
        foreach ($users as $user) {
            foreach ($achievements as $achievement) {
                $this->userAchievementRepository->saveNewUserAchievement($user->id, $achievement->id);
            }
        }
    }
}
