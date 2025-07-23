<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionAnswer extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'question_answers';
    protected $guarded = [];
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    public function userTestAnswers(): HasMany
    {
        return $this->hasMany(UserTestAnswer::class, 'answer_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/question_answer.id')),
            new ColumnLabel('text_answer', __('model_attributes/question_answer.text_answer')),
            new ColumnLabel('question_id', __('model_attributes/question_answer.question_id')),
            new ColumnLabel('is_correct', __('model_attributes/question_answer.is_correct')),
            new ColumnLabel('created_at', __('model_attributes/question_answer.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/question_answer.updated_at')),
        ];
    }
}
