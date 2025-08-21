<?php

namespace App\Models;

use App\Http\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    use Filterable;
    protected $table = 'news';
    protected $guarded = [];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    protected $casts = [
        'content' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
