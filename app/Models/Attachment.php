<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachments';
    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(Message::class, "message_id");
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
