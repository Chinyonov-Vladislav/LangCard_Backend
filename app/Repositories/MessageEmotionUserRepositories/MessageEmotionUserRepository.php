<?php

namespace App\Repositories\MessageEmotionUserRepositories;

use App\Models\MessageEmotionUser;

class MessageEmotionUserRepository implements MessageEmotionUserRepositoryInterface
{
    protected MessageEmotionUser $model;

    public function __construct(MessageEmotionUser $model)
    {
        $this->model = $model;
    }

    public function addNewEmotionToMessageFromUser(int $messageEmotionId, int $userId): MessageEmotionUser
    {
        $newMessageEmotionUser = new MessageEmotionUser();
        $newMessageEmotionUser->message_emotion_id = $messageEmotionId;
        $newMessageEmotionUser->user_id = $userId;
        $newMessageEmotionUser->save();
        return $newMessageEmotionUser;
    }

    public function changeEmotionToMessageFromUser(int $previousMessageEmotionId, int $newMessageEmotionId, int $userId): void
    {
        $this->model->where("user_id", "=",$userId)->where("message_emotion_id", "=", $previousMessageEmotionId)->update(["message_emotion_id"=>$newMessageEmotionId]);
    }

    public function removeEmotionForMessageFromUser(int $messageEmotionId, int $userId): void
    {
        $this->model->where("user_id", "=",$userId)->where("message_emotion_id", "=", $messageEmotionId)->delete();
    }
}
