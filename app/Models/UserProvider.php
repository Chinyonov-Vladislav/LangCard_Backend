<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProvider extends Model
{
    protected $table = 'user_providers';
    protected $guarded = [];

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
