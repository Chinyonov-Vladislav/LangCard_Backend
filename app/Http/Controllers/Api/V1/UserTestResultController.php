<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TestRequests\EndTestRequest;
use App\Http\Requests\Api\V1\TestRequests\StartTestRequest;
use App\Http\Resources\V1\QuestionResources\QuestionResource;
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
                                TestRepositoryInterface           $testRepository,
                                UserRepositoryInterface           $userRepository,
                                QuestionRepositoryInterface       $questionRepository,
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


    /**
     * @OA\Post(
     *     path="/tests/start",
     *     summary="Начало теста",
     *     description="Запускает тест по его идентификатору. Возвращает ID попытки и список вопросов для прохождения.",
     *     tags={"Тестирование (прохождение тестов на сайте)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StartTestRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тест успешно запущен",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Тест с id = 101 был запущен пользователем с id = 55"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="attemptId", type="integer", example=125),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(
     *         response=404,
     *         description="Тест не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Тест с id = 101 не найден"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *      response=422,
     *      description="Validation error",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          @OA\Property(property="errors", type="object",
     *              @OA\Property(property="testId", type="array",
     *                      @OA\Items(type="string", example="Поле \'testId\' обязательно для заполнения.")
     *              )
     *          )
     *      )
     *  )
     * )
     */

    public function start(StartTestRequest $request)
    {
        if (!$this->testRepository->isExistTestById($request->testId)) {
            return ApiResponse::error(__('api.test_not_found', ['id' => $request->testId]), null, 404);
        }
        $isPremiumTest = $this->testRepository->isTestForPremiumDeck($request->testId);
        $userId = auth()->id();
        if ($isPremiumTest && !$this->userRepository->hasUserActivePremiumStatusByIdUser($userId)) {
            return ApiResponse::error(__('api.test_premium_access_denied', ['testId' => $request->testId, 'userId' => $userId]), null, 403);
        }
        //проверка на количество попыток
        $testInfo = $this->testRepository->getTestById($request->testId);
        $countOfAttemptsTestByUser = $this->userTestResultRepository->getCountAttemptsOfTestByUserId($request->testId, $userId);
        if ($testInfo->count_attempts !== null && $countOfAttemptsTestByUser >= $testInfo->count_attempts) {
            return ApiResponse::error(__('api.test_attempts_exhausted', ['testId' => $request->testId, 'userId' => $userId]), null, 403);
        }
        //
        $currentTime = Carbon::now();
        $newUserTestResultId = $this->userTestResultRepository->saveNewUserTestResult($currentTime, $userId, $request->testId, $countOfAttemptsTestByUser + 1);
        return ApiResponse::success(__('api.test_started_by_user', ['testId' => $request->testId, 'userId' => $userId]),
            (object)['attemptId' => $newUserTestResultId]);
    }


    /**
     * @OA\Get(
     *     path="/tests/questionsForTest/{attemptId}",
     *     summary="Получение списка вопросов для теста",
     *     description="Возвращает список вопросов по идентификатору попытки прохождения теста. Доступно только авторизованному пользователю, которому принадлежит попытка, и только если попытка ещё не завершена и не истекло время выполнения теста.",
     *     operationId="questionsForTest",
     *     tags={"Тестирование (прохождение тестов на сайте)"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         description="Идентификатор попытки прохождения теста.",
     *         @OA\Schema(type="integer", example=125)
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Список вопросов для теста.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Вопросы с теста = 101"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/QuestionResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(
     *          response=409,
     *          description="Ошибка бизнес-логики: попытка не принадлежит пользователю, уже завершена или истекло время теста.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="The attempt does not belong to the current user"),
     *              @OA\Property(property="errors", type="object", nullable=true)
     *          )
     *      ),
     *      @OA\Response(
     *           response=404,
     *           description="Ошибка бизнес-логики: запрашиваемая попытка не найдена",
     *           @OA\JsonContent(
     *               @OA\Property(property="status", type="string", example="error"),
     *               @OA\Property(property="message", type="string", example="Attempt does not exist"),
     *               @OA\Property(property="errors", type="object", nullable=true)
     *           )
     *       ),
     * )
     */
    public function questionsForTest($attemptId)
    {
        $infoAttempt = $this->userTestResultRepository->getUserTestResultById($attemptId);
        if($infoAttempt === null)
        {
            return  ApiResponse::error("Попытка с id = $attemptId не найдена", null, 404);
        }
        if ($infoAttempt->user_id !== auth()->id()) // проверка, что попытка принадлежит текущему пользователю
        {
            return ApiResponse::error(__('api.attempt_does_not_belong_to_auth_user', ['attemptId' => $attemptId]), null, 409);
        }
        if ($infoAttempt->finish_time !== null) // проверка, что попытка не была окончена
        {
            return ApiResponse::error(__('api.attempt_already_completed', ['attemptId' => $attemptId]), null, 409);
        }
        if ($infoAttempt->test->time_seconds !== null) {
            $endTime = Carbon::parse($infoAttempt->start_time)->addSeconds($infoAttempt->test->time_seconds);
            if ($endTime->isPast()) {
                return ApiResponse::error(__('api.test_time_expired', ['testId' => $infoAttempt->test->id]), null, 409);
            }
        }
        $questionsForTest = $this->questionRepository->getQuestionsForTest($infoAttempt->test->id);
        return ApiResponse::success("Вопросы с теста = {$infoAttempt->test->id}", (object)['items' => QuestionResource::collection($questionsForTest)]);
    }


    /**
     * @OA\Post(
     *     path="/tests/end",
     *     summary="Завершение теста",
     *     description="Завершает тестовую попытку с передачей ответов пользователя. Проверяет валидность попытки, принадлежность текущему пользователю и срок действия теста.",
     *     operationId="endTest",
     *     tags={"Тестирование (прохождение тестов на сайте)"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EndTestRequest")
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Результаты завершения теста с процентом правильных ответов.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Результаты теста для attemptId=123"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="results",
     *                     type="object",
     *                     @OA\Property(property="percent", type="integer", example=85),
     *                     @OA\Property(property="total_count_questions", type="integer", example=20),
     *                     @OA\Property(property="correct_count_answers", type="integer", example=17)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(
     *         response=404,
     *         description="Попытка теста не найдена.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Attempt not found"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Ошибка бизнес-логики: попытка не принадлежит пользователю, уже завершена или истекло время теста.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Attempt does not belong to user or attempt already completed or test time expired"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *      @OA\Response(
     *      response=422,
     *      description="Validation error",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          @OA\Property(property="errors", type="object",
     *              @OA\Property(property="attemptId", type="array",
     *                  @OA\Items(type="string", example="Поле 'attemptId' обязательно для заполнения.")
     *              ),
     *              @OA\Property(property="answers", type="array",
     *                  @OA\Items(type="string", example="Поле 'answers' обязательно для заполнения.")
     *              ),
     *              @OA\Property(property="answers.*.question_id", type="array",
     *                  @OA\Items(type="string", example="Поле 'question_id' обязательно для заполнения в каждом элементе массива answers.")
     *              ),
     *              @OA\Property(property="answers.*.answer_id", type="array",
     *                  @OA\Items(type="string", example="Поле 'answer_id' должно быть целым числом в каждом элементе массива answers.")
     *              )
     *          )
     *      )
     *    )
     * )
     */
    public function end(EndTestRequest $request)
    {
        $infoAttempt = $this->userTestResultRepository->getUserTestResultById($request->attemptId);
        if ($infoAttempt === null) //проверка наличия попытки
        {
            return ApiResponse::error(__('api.attempt_not_found', ['attemptId' => $request->attemptId]), null, 404);
        }
        if ($infoAttempt->user_id !== auth()->id()) // проверка, что попытка принадлежит текущему пользователю
        {
            return ApiResponse::error(__('api.attempt_does_not_belong_to_auth_user', ['attemptId' => $request->attemptId]), null, 409);
        }
        if ($infoAttempt->finish_time !== null) // проверка, что попытка не была окончена
        {
            return ApiResponse::error(__('api.attempt_already_completed', ['attemptId' => $request->attemptId]), null, 409);
        }
        if ($infoAttempt->test->time_seconds) {
            $endTime = Carbon::parse($infoAttempt->start_time)->addSeconds($infoAttempt->test->time_seconds)->addMinutes();
            if ($endTime->isPast()) {
                return ApiResponse::error(__('api.test_time_expired', ['testId' => $infoAttempt->test->id]), null, 409);
            }
        }
        $countCorrectAnswers = 0;
        foreach ($request->answers as $answer) {
            // проверка, что вопрос, на который предоставляется ответ, существует в рамках теста
            if (!$this->questionRepository->isExistQuestionByIdInTest($answer['question_id'], $infoAttempt->test->id)) {
                continue;
            }
            $answerFromDB = $this->questionAnswerRepository->getAnswerById($answer['answer_id']);
            // проверка, что предоставленный ответ является возможным ответом на вопрос
            if ($answerFromDB->question_id !== $answer['question_id']) {
                continue;
            }
            $this->userTestAnswerRepository->saveNewUserTestAnswer($request->attemptId, $answer['question_id'], $answer['answer_id'], $answerFromDB->is_correct);
            if ($answerFromDB->is_correct) {
                $countCorrectAnswers++;
            }
        }
        $countAllQuestions = $this->testRepository->getCountQuestionInTest($infoAttempt->test->id);
        $percent = $countAllQuestions > 0 ? round(($countCorrectAnswers / $countAllQuestions) * 100) : 0;
        $this->userTestResultRepository->updateUserTestResultAfterEnding(Carbon::now(), $percent, $request->attemptId);
        $questionsForTest = $this->questionRepository->getQuestionsForTest($infoAttempt->test->id);
        foreach ($questionsForTest as $question) {
            if (!$this->userTestAnswerRepository->isExistAnswerForQuestionInAttemptOfTest($question->id, $request->attemptId)) {
                $this->userTestAnswerRepository->saveNewUserTestAnswer($request->attemptId, $question->id, null, false);
            }
        }
        $shortResults = (object)['percent' => $percent, 'total_count_questions' => $countAllQuestions, 'correct_count_answers' => $countCorrectAnswers];
        return ApiResponse::success(__('api.test_results_for_attempt', ['testId' => $infoAttempt->test->id, 'attemptId' => $infoAttempt->id]), (object)['results' => $shortResults]);
    }
}
