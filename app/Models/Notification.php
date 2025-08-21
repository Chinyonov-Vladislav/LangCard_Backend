<?php

namespace App\Models;

use App\Http\Filters\Filterable;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use Filterable;
    protected $table = 'notifications';
    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'data' => 'array',      // автоматически преобразует массив ↔ JSON
        'read_at' => 'datetime' // автоматически преобразует в Carbon
    ];
}
