<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserTestAnswerResources\UserTestAnswerResource;
use App\Http\Resources\V1\UserTestResultResources\UserTestResultResource;
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


    /**
     * @OA\Get(
     *     path="/answers/{attemptId}",
     *     summary="Получить ответы пользователя в попытке прохождения теста",
     *     description="Возвращает все ответы пользователя на вопросы в рамках указанной попытки прохождения теста. Доступно только владельцу попытки.",
     *     operationId="getAnswersInAttempt",
     *     tags={"Ответы"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         description="ID попытки прохождения теста",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список ответов пользователя для указанной попытки",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ответы пользователя для попытки с id = 15"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="attempt", ref="#/components/schemas/UserTestResultResource"),
     *                 @OA\Property(
     *                     property="questionsWithAnswers",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/UserTestAnswerResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Попытка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Попытка с id = 15 не найдена"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Попытка не принадлежит авторизованному пользователю",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Попытка с id = 15 не принадлежит авторизованному пользователю"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Попытка ещё не завершена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Попытка с id = 15 ещё не завершена"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function getAnswersInAttempt($attemptId)
    {
        $attemptInfo = $this->userTestResultRepository->getUserTestResultById($attemptId);
        if($attemptInfo === null)
        {
            return ApiResponse::error(__('api.attempt_not_found', ['attemptId'=>$attemptId]), null, 404);
        }
        if(auth()->id() !== $attemptInfo->user_id)
        {
            return ApiResponse::error(__('api.attempt_does_not_belong_to_auth_user', ['attemptId'=>$attemptId]), null, 409);
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
