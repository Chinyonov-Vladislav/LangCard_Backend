<?php

namespace App\Repositories\MessageRepositories;

use App\Models\Message;

class MessageRepository implements MessageRepositoryInterface
{
    protected Message $model;

    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    public function saveNewMessage(int $userId, int $roomId, string $message, string $type): Message
    {
        $newMessage = new Message();
        $newMessage->user_id = $userId;
        $newMessage->room_id = $roomId;
        $newMessage->message = $message;
        $newMessage->type = $type;
        $newMessage->save();
        return $newMessage;
    }

    public function getMessage(int $id)
    {
        return $this->model->where("id", "=", $id)->first();
    }

    public function updateMessage(int $messageId, string $textMessage): void
    {
        $this->model->where("id", "=", $messageId)->update(['message' => $textMessage]);
    }

    public function deleteMessage(int $messageId): void
    {
        $this->model->where("id", "=", $messageId)->delete();
    }
}
