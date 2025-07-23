<?php

namespace App\Repositories\UserTestResultRepositories;

use App\Models\UserTestResult;
use App\Services\PaginatorService;

class UserTestResultRepository implements UserTestResultRepositoryInterface
{
    protected UserTestResult $model;
    public function __construct(UserTestResult $model)
    {
        $this->model = $model;
    }

    public function saveNewUserTestResult(string $startTime, int $userId, int $testId,int $numberAttempt, ?string $end_time=null, ?int $score = null): int
    {
        $newUserTestResult = new UserTestResult();
        $newUserTestResult->start_time = $startTime;
        $newUserTestResult->user_id = $userId;
        $newUserTestResult->test_id = $testId;
        $newUserTestResult->score = $score;
        $newUserTestResult->finish_time = $end_time;
        $newUserTestResult->number_attempt = $numberAttempt;
        $newUserTestResult->save();
        return $newUserTestResult->id;
    }

    public function updateUserTestResultAfterEnding(string $endTime, int $score, int $userTestResultId): void
    {
        $this->model->where('id','=', $userTestResultId)
            ->update(['finish_time' => $endTime, 'score' => $score]);
    }

    public function getCountAttemptsOfTestByUserId($testId, $userId): int
    {
        return $this->model->where('test_id','=',$testId)->where('user_id','=',$userId)->count();
    }

    public function getUserTestResultById(int $id): ?UserTestResult
    {
        return $this->model->with(['test'])->where('id', '=', $id)->first();
    }

    public function existStartedTestForDeck(int $deckId): bool
    {
        return $this->model->whereHas('test', function ($query) use ($deckId) {
            $query->where('deck_id', $deckId);
        })->exists();
    }

    public function getResultAttemptsOfCurrentUserWithPagination(PaginatorService $paginatorService,int $userId, int $page, int $countOnPage): array
    {
        $query = $this->model->with(['test'])->where('user_id', '=', $userId)->orderBy('created_at', 'desc');
        $data = $paginatorService->paginate($query, $countOnPage, $page);
        return ['items' => collect($data->items()), "pagination" => $paginatorService->getMetadataForPagination($data)];
    }
}
