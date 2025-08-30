<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $table = 'attachments';
    protected $guarded = [];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, "message_id");
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
