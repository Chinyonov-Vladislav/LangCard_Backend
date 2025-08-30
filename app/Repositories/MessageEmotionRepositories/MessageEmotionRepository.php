<?php

namespace App\Repositories\MessageEmotionRepositories;

use App\Models\MessageEmotion;

class MessageEmotionRepository implements MessageEmotionRepositoryInterface
{
    protected MessageEmotion $model;
    public function __construct(MessageEmotion $model)
    {
        $this->model = $model;
    }

    public function addEmotionToMessage(int $messageId, int $emotionId, int $userId): MessageEmotion
    {
        $newMessageEmotion = new MessageEmotion();
        $newMessageEmotion->message_id = $messageId;
        $newMessageEmotion->emotion_id = $emotionId;
        $newMessageEmotion->user_id = $userId;
        $newMessageEmotion->save();
        return $newMessageEmotion;
    }

    public function getMessageEmotionByUserId(int $messageId, int $emotionId, int $userId): ?MessageEmotion
    {
        return $this->model->where("message_id","=",$messageId)->where("emotion_id","=",$emotionId)->where("user_id","=",$userId)->first();
    }

    public function deleteEmotionForMessageFromUser(int $messageId, int $emotionId, int $userId): bool
    {
        return $this->model->where("message_id","=",$messageId)->where("emotion_id","=",$emotionId)->where("user_id","=",$userId)->delete();
    }

    public function updateEmotionForMessageFromUser(int $messageId, int $previousEmotionId, int $newEmotionId, int $userId): bool
    {
        return $this->model->where("message_id","=",$messageId)
            ->where("emotion_id","=",$previousEmotionId)
            ->where("user_id","=",$userId)
            ->update(["emotion_id" => $newEmotionId]);
    }
}
