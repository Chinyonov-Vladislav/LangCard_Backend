<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageEmotion extends Model
{
    protected $table = 'message_emotions';
    protected $guarded = [];


    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    public function emotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class, 'emotion_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function casts(): array
    {
        return [

        ];
    }

}
