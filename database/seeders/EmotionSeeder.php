<?php

namespace Database\Seeders;

use App\Repositories\EmotionRepositories\EmotionRepositoryInterface;
use Illuminate\Database\Seeder;

class EmotionSeeder extends Seeder
{
    protected EmotionRepositoryInterface $emotionRepository;

    public function __construct(EmotionRepositoryInterface $emotionRepository)
    {
        $this->emotionRepository = $emotionRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emotions = [
            ['name' => 'like', 'icon' => '👍'],
            ['name' => 'love', 'icon' => '❤️'],
            ['name' => 'laugh', 'icon' => '😂'],
            ['name' => 'sad', 'icon' => '😢'],
            ['name' => 'angry', 'icon' => '😡'],
            ['name' => 'surprised', 'icon' => '😲'],
            ['name' => 'wow', 'icon' => '😮'],
            ['name' => 'confused', 'icon' => '😕'],
            ['name' => 'cry', 'icon' => '😭'],
            ['name' => 'kiss', 'icon' => '😘'],
            ['name' => 'heart_eyes', 'icon' => '😍'],
            ['name' => 'sleepy', 'icon' => '😴'],
            ['name' => 'sick', 'icon' => '🤢'],
            ['name' => 'party', 'icon' => '🥳'],
            ['name' => 'thinking', 'icon' => '🤔'],
            ['name' => 'neutral', 'icon' => '😐'],
            ['name' => 'clap', 'icon' => '👏'],
            ['name' => 'fire', 'icon' => '🔥'],
            ['name' => 'star', 'icon' => '⭐'],
            ['name' => 'thumbs_down', 'icon' => '👎'],
        ];
        foreach ($emotions as $emotion) {
            $this->emotionRepository->saveNewEmotion($emotion['name'], $emotion['icon']);
        }
    }
}
