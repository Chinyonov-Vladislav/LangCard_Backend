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

    public function addEmotionToMessage(int $messageId, int $emotionId): MessageEmotion
    {
        $newMessageEmotion = new MessageEmotion();
        $newMessageEmotion->message_id = $messageId;
        $newMessageEmotion->emotion_id = $emotionId;
        $newMessageEmotion->save();
        return $newMessageEmotion;
    }

    public function getMessageEmotion(int $messageId, int $emotionId): ?MessageEmotion
    {
        return $this->model->where("message_id","=",$messageId)->where("emotion_id","=",$emotionId)->first();
    }
}
