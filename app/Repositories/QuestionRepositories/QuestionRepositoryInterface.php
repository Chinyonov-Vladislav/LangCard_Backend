<?php

namespace App\Repositories\QuestionRepositories;

interface QuestionRepositoryInterface
{
    public function getQuestionsForTest($testId);
    public function isExistQuestionByIdInTest($questionId, $testId);

    public function isExistQuestionById(int $id): bool;

    public function isExistCardForQuestionInSameDeckAsTest(int $cardId, int $testId);

    public function getQuestionWithCardById(int $questionId);
    public function saveNewQuestion(?string $text, string $type, int $cardId, int $testId);
}
