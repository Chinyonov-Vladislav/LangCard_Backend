<?php

namespace Database\Seeders;

use App\Repositories\AchievementRepositories\AchievementRepository;
use App\Repositories\AchievementRepositories\AchievementRepositoryInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    protected AchievementRepositoryInterface $achievementRepository;

    public function __construct(AchievementRepositoryInterface $achievementRepository)
    {
        $this->achievementRepository = $achievementRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // первое - done
        // второе -
        // третье - done
        // четвертое - done (не до конца)
        // пятое -
        $data = [
            [
                'title' => 'Пригласи друга',
                'description' => 'Пригласи хотя бы одного друга на платформу и получи награду.',
                'icon' => null,
                'target' => 1
            ],
            [
                'title' => 'Покори тесты',
                'description' => 'Успешно пройди 5 тестов, чтобы заработать достижение.',
                'icon' => null,
                'target' => 5
            ],
            [
                'title' => 'Первая колода',
                'description' => 'Создай свою первую колоду карточек.',
                'icon' => null,
                'target' => 1
            ],
            [
                'title' => 'VIP-клуб',
                'description' => 'Оформи VIP-статус и стань частью элитного сообщества.',
                'icon' => null,
                'target' => 1
            ],
            [
                'title' => 'Новый образ',
                'description' => 'Смени аватарку профиля.',
                'icon' => null,
                'target' => 1
            ]
        ];
        foreach ($data as $item) {
            $this->achievementRepository->saveNewAchievement($item['title'], $item['description'], $item['icon'], $item['target']);
        }
    }
}
