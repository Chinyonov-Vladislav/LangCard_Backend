<?php

namespace App\Repositories\UserTestResultRepositories;

use App\Models\UserTestResult;
use App\Services\PaginatorService;

interface UserTestResultRepositoryInterface
{

    public function existStartedTestForDeck(int $deckId): bool;

    public function getResultAttemptsOfCurrentUserWithPagination(PaginatorService $paginatorService, int $userId, int $page, int $countOnPage): array;

    public function getCountAttemptsOfTestByUserId($testId, $userId): int;

    public function getUserTestResultById(int $id): ?UserTestResult;

    public function updateUserTestResultAfterEnding(string $endTime, int $score, int $userTestResultId);

    public function saveNewUserTestResult(string $startTime, int $userId, int $testId,int $numberAttempt, ?string $end_time=null, ?int $score = null): int;
}
