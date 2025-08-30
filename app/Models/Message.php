<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function emotions(): BelongsToMany
    {
        return $this->belongsToMany(Emotion::class, 'message_emotions', 'message_id', 'emotion_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function emotionUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_emotions', 'message_id', 'user_id')
            ->withPivot('emotion_id')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, "message_id");
    }

    protected function casts(): array
    {
        return [
            'created_at'=>"datetime"
        ];
    }
}
