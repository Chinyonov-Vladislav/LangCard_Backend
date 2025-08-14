<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    protected $table = 'user_achievements';
    protected $guarded = [];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, "achievement_id");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
