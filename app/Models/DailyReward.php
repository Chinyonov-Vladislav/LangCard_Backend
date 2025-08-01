<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    protected $table = 'daily_rewards';
    protected $guarded = [];

    protected function casts(): array
    {
        return [

        ];
    }
}
