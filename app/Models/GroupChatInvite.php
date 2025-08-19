<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupChatInvite extends Model
{
    protected $table = "group_chat_invites";
    protected $guarded = [];

    /**
     * Связь с комнатой
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, "room_id");
    }
    /**
     * Связь с пользователем (кому отправлено приглашение)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, "sender_user_id");
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, "recipient_user_id");
    }

}
