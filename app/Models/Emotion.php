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
        return $this->belongsToMany(Message::class, 'message_emotions', "emotion_id", "message_id")
            ->withTimestamps();
    }

    public function messageEmotions(): HasMany
    {
        return $this->hasMany(MessageEmotion::class, "emotion_id");
    }


    protected function casts(): array
    {
        return [

        ];
    }
}
