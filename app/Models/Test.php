<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int|null $time_seconds
 * @property int|null $count_attempts
 * @property int $deck_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Deck $deck
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTestResult> $userTestResults
 * @property-read int|null $user_test_results_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereCountAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereDeckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereTimeSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Test whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
