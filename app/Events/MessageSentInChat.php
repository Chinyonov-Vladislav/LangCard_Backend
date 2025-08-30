<?php

namespace App\Events;

use App\Http\Resources\V1\MessageResources\MessageResource;
use App\Models\Message;
use App\Repositories\MessageRepositories\MessageRepositoryInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


// TODO: проверить работоспособность
class MessageSentInChat
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(int $messageId)
    {
        $messageRepository = app(MessageRepositoryInterface::class);
        $this->message = $messageRepository->getMessage($messageId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("room.{$this->message->room_id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => (new MessageResource($this->message))->toArray(request())
        ];
    }
}
