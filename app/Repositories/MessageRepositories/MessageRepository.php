<?php

namespace App\Repositories\MessageRepositories;

use App\Enums\TypesMessage;
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

    public function getMessagesOfChatWithPagination(int $currentUserId, int $chatId, int $limit = 10, ?int $lastMessageId = null): array
    {
        $query = $this->model
            ->select(['id','user_id','room_id','message','type','created_at'])
            ->with(['user'=>function($query){
                $query->select(["id","name","avatar_url"]);
            }])
            ->where('room_id', $chatId)
            ->orderByDesc('id');
        if ($lastMessageId) {
            $query->where('id', '<', $lastMessageId);
        }
        $messages = $query->limit($limit + 1)->get();
        $hasMore = $messages->count() > $limit;
        if ($hasMore) {
            $messages->pop();
        }
        $userMessages = $messages->filter(
            fn ($message) => $message->type === TypesMessage::MessageFromUser->value
        );
        $userMessages->load([
            'messageEmotions' => function ($q) use ($currentUserId) {
                $q->select(['id','message_id','emotion_id'])
                    ->with('emotion:id,name,icon')
                    ->withCount('users')
                    ->when($currentUserId, function ($q) use ($currentUserId) {
                        $q->withCount([
                            'users as reacted_by_me' => fn ($q) => $q->where('user_id', $currentUserId),
                        ]);
                    })
                    ->orderByDesc('users_count')
                    ->orderBy('id');
            }
        ]);
        if ($lastMessageId === null) {
            $messages = $messages->reverse()->values();
        }
        return ["messages" => $messages, "hasMoreMessages" => $hasMore];
    }
}
