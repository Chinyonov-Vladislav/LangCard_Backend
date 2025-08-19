<?php

namespace App\Repositories\MessageRepositories;

use App\Models\Message;

interface MessageRepositoryInterface
{
    public function getMessage(int $id);

    public function updateMessage(int $messageId, string $textMessage);
    public function saveNewMessage(int $userId, int $roomId, string $message, string $type): Message;

    public function deleteMessage(int $messageId);
}
