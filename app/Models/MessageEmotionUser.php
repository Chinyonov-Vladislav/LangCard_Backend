<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageEmotionUser extends Model
{
    protected $table = 'message_emotion_users';
    protected $guarded = [];

    public function messageEmotion(): BelongsTo
    {
        return $this->belongsTo(MessageEmotion::class, "message_emotion_id");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
