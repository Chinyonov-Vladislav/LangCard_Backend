<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string|null $text
 * @property string $type
 * @property int $card_id
 * @property int $test_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuestionAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Card $card
 * @property-read \App\Models\QuestionAnswer|null $correctAnswer
 * @property-read \App\Models\Test $test
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTestAnswer> $userTestAnswers
 * @property-read int|null $user_test_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Question extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'questions';
    protected $guarded = [];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class, 'question_id');
    }
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(QuestionAnswer::class, 'question_id')->where('is_correct', '=', true);
    }
    public function userTestAnswers(): HasMany
    {
        return $this->hasMany(UserTestAnswer::class, 'question_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/question.id')),
            new ColumnLabel('text', __('model_attributes/question.text')),
            new ColumnLabel('type', __('model_attributes/question.type')),
            new ColumnLabel('card_id', __('model_attributes/question.card_id')),
            new ColumnLabel('test_id', __('model_attributes/question.test_id')),
            new ColumnLabel('created_at', __('model_attributes/question.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/question.updated_at')),
        ];
    }
}
