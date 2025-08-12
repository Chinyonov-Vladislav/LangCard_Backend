<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStatus extends Model
{
    protected $table = 'job_statuses';

    protected $guarded = [];

    // Автоматическое преобразование JSON-полей в массивы и обратно
    protected $casts = [
        'initial_data' => 'array',
        'result' => 'array',
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
