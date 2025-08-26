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
        return $this->belongsTo(Message::class, "message_id");
    }

    public function emotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class, "emotion_id");
    }

    // Пользователи, которые поставили данную эмоцию к сообщению
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'message_emotion_users',
            'message_emotion_id',
            'user_id'
        )->withTimestamps();
    }

    protected function casts(): array
    {
        return [

        ];
    }

}
