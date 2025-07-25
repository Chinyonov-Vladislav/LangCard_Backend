<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_test_result_id
 * @property int $question_id
 * @property int|null $answer_id
 * @property int $is_correct
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\QuestionAnswer|null $questionAnswer
 * @property-read \App\Models\UserTestResult $userTestResult
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTestAnswer whereUserTestResultId($value)
 * @mixin \Eloquent
 */
class UserTestAnswer extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'user_test_answers';
    protected $guarded = [];

    public function questionAnswer(): BelongsTo
    {
        return $this->belongsTo(QuestionAnswer::class, 'answer_id');
    }
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    public function userTestResult(): BelongsTo
    {
        return $this->belongsTo(UserTestResult::class, 'user_test_result_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/user_test_answer.id')),
            new ColumnLabel('user_test_result_id', __('model_attributes/user_test_answer.user_test_result_id')),
            new ColumnLabel('question_id', __('model_attributes/user_test_answer.question_id')),
            new ColumnLabel('answer_id', __('model_attributes/user_test_answer.answer_id')),
            new ColumnLabel('is_correct', __('model_attributes/user_test_answer.is_correct')),
            new ColumnLabel('created_at', __('model_attributes/user_test_answer.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/user_test_answer.updated_at')),
        ];
    }
}
