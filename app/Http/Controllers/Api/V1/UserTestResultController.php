<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TestRequests\EndTestRequest;
use App\Http\Requests\Api\V1\TestRequests\StartTestRequest;
use App\Http\Resources\v1\QuestionResources\QuestionResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\QuestionAnswerRepository\QuestionAnswerRepositoryInterface;
use App\Repositories\QuestionRepositories\QuestionRepositoryInterface;
use App\Repositories\TestRepositories\TestRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Repositories\UserTestAnswerRepositories\UserTestAnswerRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use Carbon\Carbon;

class UserTestResultController extends Controller
{
    protected QuestionRepositoryInterface $questionRepository;
    protected UserRepositoryInterface $userRepository;

    protected UserTestResultRepositoryInterface $userTestResultRepository;

    protected TestRepositoryInterface $testRepository;

    protected QuestionAnswerRepositoryInterface $questionAnswerRepository;

    protected UserTestAnswerRepositoryInterface $userTestAnswerRepository;

    public function __construct(UserTestResultRepositoryInterface $userTestResultRepository,
                                TestRepositoryInterface $testRepository,
                                UserRepositoryInterface $userRepository,
                                QuestionRepositoryInterface $questionRepository,
                                UserTestAnswerRepositoryInterface $userTestAnswerRepository,
                                QuestionAnswerRepositoryInterface $questionAnswerRepository)
    {
        $this->userTestResultRepository = $userTestResultRepository;
        $this->testRepository = $testRepository;
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->userTestAnswerRepository = $userTestAnswerRepository;
        $this->questionAnswerRepository = $questionAnswerRepository;
    }

    public function start(StartTestRequest $request)
    {
        if(!$this->testRepository->isExistTestById($request->testId))
        {
            return ApiResponse::error(__('api.test_not_found', ['id'=>$request->testId]), null, 404);
        }
        $isPremiumTest = $this->testRepository->isTestForPremiumDeck($request->testId);
        $userId = auth()->id();
        if($isPremiumTest && !$this->userRepository->hasUserActivePremiumStatusByIdUser($userId))
        {
            return ApiResponse::error(__('api.test_premium_access_denied', ['testId'=>$request->testId,'userId'=>$userId]), null, 403);
        }
        //проверка на количество попыток
        $testInfo = $this->testRepository->getTestById($request->testId);
        $countOfAttemptsTestByUser = $this->userTestResultRepository->getCountAttemptsOfTestByUserId($request->testId, $userId);
        if ($testInfo->count_attempts !== null && $countOfAttemptsTestByUser >= $testInfo->count_attempts) {
            return ApiResponse::error(__('api.test_attempts_exhausted', ['testId'=>$request->testId,'userId'=>$userId]), null, 403);
        }
        //
        $currentTime = Carbon::now();
        $newUserTestResultId =$this->userTestResultRepository->saveNewUserTestResult($currentTime, $userId, $request->testId, $countOfAttemptsTestByUser+1);
        $questionsForTest = $this->questionRepository->getQuestionsForTest($request->testId);
        return ApiResponse::success(__('api.test_started_by_user', ['testId'=>$request->testId,'userId'=>$userId]),
            (object)['attemptId'=>$newUserTestResultId,'items'=>QuestionResource::collection($questionsForTest)]);
    }
    public function end(EndTestRequest $request)
    {
        $infoAttempt = $this->userTestResultRepository->getUserTestResultById($request->attemptId);
        if($infoAttempt === null) //проверка наличия попытки
        {
            return ApiResponse::error(__('api.attempt_not_found', ['attemptId'=>$request->attemptId]), null, 404);
        }
        if($infoAttempt->user_id !== auth()->id()) // проверка, что попытка принадлежит текущему пользователю
        {
            return ApiResponse::error(__('api.attempt_does_not_belong_to_auth_user', ['attemptId'=>$request->attemptId]), null, 403);
        }
        if($infoAttempt->finish_time !== null) // проверка, что попытка не была окончена
        {
            return ApiResponse::error(__('api.attempt_already_completed',['attemptId'=>$request->attemptId]), null, 403);
        }
        if (!$request->automatic && $infoAttempt->test->time_seconds) {
            $endTime = Carbon::parse($infoAttempt->start_time)->addSeconds($infoAttempt->test->time_seconds);
            if ($endTime->isPast()) {
                return ApiResponse::error(__('api.test_time_expired', ['testId' => $infoAttempt->test->id]), null, 403);
            }
        }
        $countCorrectAnswers = 0;
        foreach ($request->answers as $answer)
        {
            // проверка, что вопрос, на который предоставляется ответ, существует в рамках теста
            if(!$this->questionRepository->isExistQuestionByIdInTest($answer['question_id'], $infoAttempt->test->id))
            {
                continue;
            }
            $answerFromDB = $this->questionAnswerRepository->getAnswerById($answer['answer_id']);
            // проверка, что предоставленный ответ является возможным ответом на вопрос
            if($answerFromDB->question_id !== $answer['question_id'])
            {
                continue;
            }
            $this->userTestAnswerRepository->saveNewUserTestAnswer($request->attemptId, $answer['question_id'], $answer['answer_id'], $answerFromDB->is_correct);
            if($answerFromDB->is_correct)
            {
                $countCorrectAnswers++;
            }
        }
        $countAllQuestions = $this->testRepository->getCountQuestionInTest($infoAttempt->test->id);
        $percent = $countAllQuestions > 0 ? round(($countCorrectAnswers / $countAllQuestions) * 100) : 0;
        $this->userTestResultRepository->updateUserTestResultAfterEnding(Carbon::now(), $percent,$request->attemptId);
        $questionsForTest = $this->questionRepository->getQuestionsForTest($infoAttempt->test->id);
        foreach ($questionsForTest as $question)
        {
            if(!$this->userTestAnswerRepository->isExistAnswerForQuestionInAttemptOfTest($question->id, $request->attemptId))
            {
                $this->userTestAnswerRepository->saveNewUserTestAnswer($request->attemptId, $question->id, null, false);
            }
        }
        $shortResults = (object)['percent'=>$percent, 'total_count_questions'=>$countAllQuestions, 'correct_count_answers'=>$countCorrectAnswers];
        return ApiResponse::success(__('api.test_results_for_attempt', ['testId'=>$infoAttempt->test->id,'attemptId'=>$infoAttempt->id]), (object)['results'=>$shortResults]);
    }
}
