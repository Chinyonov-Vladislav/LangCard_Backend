<?php

namespace App\Repositories\UserTestAnswerRepositories;

interface UserTestAnswerRepositoryInterface
{
    public function getAnswersForAttemptId(int $attemptId, bool $canAddCorrectAnswer);
    public function isExistAnswerForQuestionInAttemptOfTest(int $questionId, int $attemptId): bool;
    public function saveNewUserTestAnswer(int $user_test_result_id, int $question_id, ?int $answer_id, bool $is_correct): void;
}
