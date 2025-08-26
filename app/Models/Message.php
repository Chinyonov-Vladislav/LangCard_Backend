<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $table = 'messages';
    protected $guarded = [];

    /**
    * Комната, к которой относится сообщение
    **/
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Автор сообщения
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emotions()
    {
        return $this->belongsToMany(Emotion::class, 'message_emotions',
            "message_id", "emotion_id")->withTimestamps();
    }
    public function messageEmotions(): HasMany
    {
        return $this->hasMany(MessageEmotion::class, "message_id");
    }

    public function reactionUsers()
    {
        return $this->hasManyThrough(
            MessageEmotionUser::class, // конечная модель
            MessageEmotion::class,     // промежуточная модель
            'message_id',              // FK на MessageEmotion (вторая таблица)
            'message_emotion_id',      // FK на MessageEmotionUser (конечная таблица)
            'id',                      // локальный ключ в Message
            'id'                       // локальный ключ в MessageEmotion
        );
    }

    protected function casts(): array
    {
        return [
            'created_at'=>"datetime"
        ];
    }
}
