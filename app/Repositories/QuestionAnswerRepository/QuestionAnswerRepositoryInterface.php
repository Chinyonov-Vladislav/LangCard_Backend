<?php

namespace App\Repositories\QuestionAnswerRepository;

use App\Models\QuestionAnswer;

interface QuestionAnswerRepositoryInterface
{
    public function getAnswerById(int $answerId): ?QuestionAnswer;
    public function isExistAnswerForQuestionByTextAnswer(string $textAnswer, int $questionId): bool;
    public function saveNewAnswer(string $answer, int $questionId, bool $isCorrect);
}
