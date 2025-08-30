<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emotion extends Model
{
    protected $table = 'emotions';
    protected $guarded = [];

    public function messages()
    {
        return $this->belongsToMany(Message::class, 'message_emotions', 'emotion_id', 'message_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'message_emotions', 'emotion_id', 'user_id')
            ->withPivot('message_id')
            ->withTimestamps();
    }

    public function messageEmotions()
    {
        return $this->hasMany(MessageEmotion::class, 'emotion_id', 'id');
    }

    public function messageEmotionsByUser(int $userId)
    {
        return $this->hasMany(MessageEmotion::class, 'emotion_id', 'id')
            ->where('user_id', $userId);
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
