<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function casts(): array
    {
        return [
            'created_at'=>"datetime"
        ];
    }
}
