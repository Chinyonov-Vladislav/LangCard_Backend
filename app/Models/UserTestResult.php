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
 * @property int|null $score
 * @property string $start_time
 * @property string|null $finish_time
 * @property int $number_attempt
 * @property int $user_id
 * @property int $test_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTestAnswer> $userTestAnswers
 * @property-read int|null $user_test_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereNumberAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestResult whereUserId($value)
 * @mixin \Eloquent
 */
class UserTestResult extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'user_test_results';
    protected $guarded = [];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
    public function userTestAnswers(): HasMany
    {
        return $this->hasMany(UserTestAnswer::class, 'user_test_result_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/user_test_result.id')),
            new ColumnLabel('score', __('model_attributes/user_test_result.score')),
            new ColumnLabel('start_time', __('model_attributes/user_test_result.start_time')),
            new ColumnLabel('finish_time', __('model_attributes/user_test_result.finish_time')),
            new ColumnLabel('user_id', __('model_attributes/user_test_result.user_id')),
            new ColumnLabel('test_id', __('model_attributes/user_test_result.test_id')),
            new ColumnLabel('number_attempt', __('model_attributes/user_test_result.number_attempt')),
            new ColumnLabel('created_at', __('model_attributes/user_test_result.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/user_test_result.updated_at')),
        ];
    }
}
