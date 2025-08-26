<?php

namespace App\Repositories\MessageEmotionRepositories;

use App\Models\MessageEmotion;

interface MessageEmotionRepositoryInterface
{
    public function getMessageEmotion(int $messageId, int $emotionId): ?MessageEmotion;

    public function addEmotionToMessage(int $messageId, int $emotionId);
}
