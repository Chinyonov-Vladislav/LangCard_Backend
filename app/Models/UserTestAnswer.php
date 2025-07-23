<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
