<?php

namespace App\Repositories\QuestionAnswerRepository;

use App\Models\QuestionAnswer;

class QuestionAnswerRepository implements QuestionAnswerRepositoryInterface
{
    protected QuestionAnswer $model;

    public function __construct(QuestionAnswer $model)
    {
        $this->model = $model;
    }

    public function saveNewAnswer(string $answer, int $questionId, bool $isCorrect): void
    {
        $newAnswerForQuestion = new QuestionAnswer();
        $newAnswerForQuestion->text_answer = $answer;
        $newAnswerForQuestion->question_id = $questionId;
        $newAnswerForQuestion->is_correct = $isCorrect;
        $newAnswerForQuestion->save();
    }

    public function isExistAnswerForQuestionByTextAnswer(string $textAnswer, int $questionId): bool
    {
        return $this->model->where('text_answer','=',$textAnswer)->where('question_id','=',$questionId)->exists();
    }

    public function getAnswerById(int $answerId): ?QuestionAnswer
    {
        return $this->model->where('id','=',$answerId)->first();
    }
}
