<?php

namespace App\Repositories\EmotionRepositories;

use App\Models\Emotion;

interface EmotionRepositoryInterface
{
    public function getEmotions();

    public function getEmotion(int $id): ?Emotion;

    public function saveNewEmotion(string $name, string $icon): Emotion;

}
