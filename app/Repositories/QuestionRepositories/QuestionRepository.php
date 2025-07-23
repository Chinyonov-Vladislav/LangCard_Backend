<?php

namespace App\Repositories\QuestionRepositories;

use App\Models\Card;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

class QuestionRepository implements QuestionRepositoryInterface
{
    protected Question $model;
    public function __construct(Question $model)
    {
        $this->model = $model;
    }
    public function isExistCardForQuestionInSameDeckAsTest(int $cardId, int $testId)
    {
        return Card::where('id', $cardId)
            ->whereHas('deck', function ($query1) use ($testId) {
                $query1->whereHas('tests', function ($query2) use ($testId) {
                    $query2->where('id', $testId);
                });
            })->exists();
    }

    public function saveNewQuestion(?string $text, string $type, int $cardId, int $testId)
    {
        $newQuestionForTest = new Question();
        $newQuestionForTest->text = $text;
        $newQuestionForTest->type = $type;
        $newQuestionForTest->card_id = $cardId;
        $newQuestionForTest->test_id = $testId;
        $newQuestionForTest->save();
    }

    public function isExistQuestionById(int $id): bool
    {
        return $this->model->where('id','=', $id)->exists();
    }

    public function getQuestionWithCardById(int $questionId)
    {
        return $this->model->with(['card'])->where('id','=', $questionId)->first();
    }

    public function isExistQuestionByIdInTest($questionId, $testId)
    {
        return $this->model->where('id','=',$questionId)->where('test_id','=',$testId)->exists();
    }

    public function getQuestionsForTest($testId): Collection
    {
        return $this->model->with(['card', 'answers'])->where('test_id','=', $testId)->get();
    }
}
