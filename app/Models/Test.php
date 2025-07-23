<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'tests';
    protected $guarded = [];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'deck_id');
    }
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'test_id');
    }
    public function userTestResults(): HasMany
    {
        return $this->hasMany(UserTestResult::class, 'test_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/test.id')),
            new ColumnLabel('name', __('model_attributes/test.name')),
            new ColumnLabel('time_seconds', __('model_attributes/test.time_seconds')),
            new ColumnLabel('count_attempts', __('model_attributes/test.count_attempts')),
            new ColumnLabel('deck_id', __('model_attributes/test.deck_id')),
            new ColumnLabel('created_at', __('model_attributes/test.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/test.updated_at')),
        ];
    }
}
