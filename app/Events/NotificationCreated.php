<?php

namespace App\Events;

use App\Enums\TypesNotification;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private array $data;
    private TypesNotification $type;
    /**
     * Create a new event instance.
     */
    public function __construct(User $user, array $data, TypesNotification $type)
    {
        $this->user = $user;
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->user->id),
        ];
    }

    /**
     * Данные, которые будут отправлены на фронт.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type->value,
            'data' => $this->data,
        ];
    }
}
