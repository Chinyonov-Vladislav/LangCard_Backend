<?php

namespace App\Repositories\MessageEmotionRepositories;

use App\Models\MessageEmotion;

interface MessageEmotionRepositoryInterface
{
    public function getMessageEmotionByUserId(int $messageId, int $emotionId, int $userId): ?MessageEmotion;
    public function addEmotionToMessage(int $messageId, int $emotionId, int $userId): MessageEmotion;
    public function deleteEmotionForMessageFromUser(int $messageId, int $emotionId, int $userId): bool;

    public function updateEmotionForMessageFromUser(int $messageId, int $previousEmotionId,int $newEmotionId, int $userId): bool;
}
