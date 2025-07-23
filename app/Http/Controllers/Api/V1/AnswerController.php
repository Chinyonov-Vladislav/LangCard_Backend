<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserTestAnswerResources\UserTestAnswerResource;
use App\Http\Resources\v1\UserTestResultResources\UserTestResultResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserTestAnswerRepositories\UserTestAnswerRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;

class AnswerController extends Controller
{
    protected UserTestResultRepositoryInterface $userTestResultRepository;

    protected UserTestAnswerRepositoryInterface $userTestAnswerRepository;

    public function __construct(UserTestResultRepositoryInterface $userTestResultRepository, UserTestAnswerRepositoryInterface $userTestAnswerRepository)
    {
        $this->userTestResultRepository = $userTestResultRepository;
        $this->userTestAnswerRepository = $userTestAnswerRepository;
    }

    public function getAnswersInAttempt($attemptId)
    {
        $attemptInfo = $this->userTestResultRepository->getUserTestResultById($attemptId);
        if($attemptInfo === null)
        {
            return ApiResponse::error(__('api.attempt_not_found', ['attemptId'=>$attemptId]), null, 404);
        }
        if(auth()->id() !== $attemptInfo->user_id)
        {
            return ApiResponse::error(__('api.attempt_does_not_belong_to_auth_user', ['attemptId'=>$attemptId]), null, 403);
        }
        if($attemptInfo->finish_time === null)
        {
            return ApiResponse::error(__('api.attempt_not_completed', ['attemptId'=>$attemptId]), null, 422);
        }
        $maxAttempts = $attemptInfo->test->count_attempts;
        $canAddCorrectAnswers = $maxAttempts !== null && $maxAttempts === $this->userTestResultRepository->getCountAttemptsOfTestByUserId($attemptInfo->test_id, auth()->id());
        $answers = $this->userTestAnswerRepository->getAnswersForAttemptId($attemptId, $canAddCorrectAnswers);
        return ApiResponse::success(__('api.user_answers_for_attempt', ['attemptId'=>$attemptId]),(object)['attempt'=>new UserTestResultResource($attemptInfo), 'questionsWithAnswers'=>UserTestAnswerResource::collection($answers)]);
    }
}
