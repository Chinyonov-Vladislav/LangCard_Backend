<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatuses;
use App\Enums\NameJobsEnum;
use App\Enums\TypeInfoAboutDeck;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CardRequests\AddingVoiceForCardRequest;
use App\Http\Requests\Api\V1\CardRequests\CreatingCardForDeckRequest;
use App\Http\Requests\Api\V1\ExampleRequests\AddingExampleRequest;
use App\Http\Requests\Api\V1\ExampleRequests\AddingMultipleExamplesRequest;
use App\Http\Resources\V1\ExampleResources\InfoSavingExampleUsingWordInCardResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\GeneratingVoiceJob;
use App\Repositories\AudiofileRepositories\AudiofileRepositoryInterface;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\ExampleRepositories\ExampleRepositoryInterface;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use Str;
use Throwable;

class CardController extends Controller
{
    protected DeckRepositoryInterface $deckRepository;

    protected CardRepositoryInterface $cardRepository;

    protected VoiceRepositoryInterface $voiceRepository;

    protected AudiofileRepositoryInterface $audiofileRepository;

    protected ExampleRepositoryInterface $exampleRepository;

    protected JobStatusRepositoryInterface $jobStatusRepository;


    protected UserTestResultRepositoryInterface $userTestResultRepository;

    public function __construct(DeckRepositoryInterface           $deckRepository,
                                CardRepositoryInterface           $cardRepository,
                                VoiceRepositoryInterface          $voiceRepository,
                                AudiofileRepositoryInterface      $audiofileRepository,
                                ExampleRepositoryInterface        $exampleRepository,
                                UserTestResultRepositoryInterface $userTestResultRepository,
                                JobStatusRepositoryInterface      $jobStatusRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->voiceRepository = $voiceRepository;
        $this->audiofileRepository = $audiofileRepository;
        $this->exampleRepository = $exampleRepository;
        $this->userTestResultRepository = $userTestResultRepository;
        $this->jobStatusRepository = $jobStatusRepository;
    }


    /**
     * @OA\Post(
     *     path="/cards",
     *     tags={"Карточки"},
     *     summary="Создание карточки для колоды",
     *     operationId="createCardForDeck",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatingCardForDeckRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Карточка успешно создана",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Карточка для колоды с id = 12 была успешно создана"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Колода не найдена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Колода не найдена"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является создателем колоды",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Авторизованный пользователь не является создателем колоды"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации входных данных",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="deck_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="Выбранная колода не существует.")
     *                 ),
     *                 @OA\Property(
     *                     property="word",
     *                     type="array",
     *                     @OA\Items(type="string", example="Поле 'Word' обязательно для заполнения.")
     *                 ),
     *                 @OA\Property(
     *                      property="translate",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле 'Translate' обязательно для заполнения.")
     *                  ),
     *                 @OA\Property(
     *                       property="imagePath",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'imagePath' должно быть строкой.")
     *                   ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Текст ошибки"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function createCardForDeck(CreatingCardForDeckRequest $request)
    {
        try {
            $deckInfo = $this->deckRepository->getDeckById($request->deck_id, TypeInfoAboutDeck::maximum);
            if ($deckInfo === null) {
                return ApiResponse::error('Колода не найдена', null, 404);
            }
            if ($deckInfo->user_id !== auth()->id()) {
                return ApiResponse::error('Авторизованный пользователь не является создателем колоды', null, 409);
            }
            $this->cardRepository->saveNewCard($request->word, $request->translate, $request->imagePath, $request->deck_id);
            return ApiResponse::success("Карточка для колоды с id = $request->deck_id была успешно создана");
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/cards/{id}",
     *     operationId="deleteCard",
     *     tags={"Карточки"},
     *     summary="Удаление карточки",
     *     description="Позволяет автору колоды удалить карточку, если она не используется в начатых тестах",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID карточки для удаления",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Карточка успешно удалена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Карточка с id = 123 была успешно удалена"),
     *             @OA\Property(property="data", type="object",nullable = true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Карточка не найдена",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status", "message", "errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Карточка с id = 123 не найдена"),
     *             @OA\Property(property="errors", type="object",nullable = true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Невозможно удалить карточку | Карточка используется в начатых тестах",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status", "message", "errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Текущий пользователь не является автором колоды, к которой принадлежит удаляемая карточка, поэтому её удаление невозможно."),
     *             @OA\Property(property="errors", type="object", example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function deleteCard(int $id)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if ($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if ($card->deck->user_id !== auth()->id()) {
            return ApiResponse::error("Текущий пользователь не является автором колоды, к которой принадлежит удаляемая карточка, поэтому её удаление невозможно.", null, 409);
        }
        if ($this->userTestResultRepository->existStartedTestForCard($id)) {
            return ApiResponse::error("Карточка не может быть удалена, так как пользователями уже были начаты тесты, в которых она используется", null, 409);
        }
        $this->cardRepository->delete($card);
        return ApiResponse::success("Карточка с id = $id была успешно удалена");
    }


    /**
     * @OA\Post(
     *     path="/cards/{id}/addVoices",
     *     tags={"Карточки"},
     *     summary="Добавление голосов для карточки",
     *     description="Запускает фоновую задачу на генерацию произношения слов для указанной карточки.",
     *     operationId="addVoicesForCard",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID карточки, для которой будут добавлены голоса",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingVoiceForCardRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Генерация голосов успешно запущена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Генерация произношения слов для карточки начата"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="job_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Карточка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Карточка с id = 15 не найдена"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является владельцем колоды",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Текущий пользователь не является автором колоды, к которой принадлежит удаляемая карточка, поэтому её удаление невозможно."
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации входных данных",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="originalVoices",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле 'originalVoices' должно быть массивом")
     *                  ),
     *                 @OA\Property(
     *                     property="originalVoices.*",
     *                     type="array",
     *                     @OA\Items(type="string", example="Выбранный оригинальный голос не существует.")
     *                 ),
     *                  @OA\Property(
     *                       property="targetVoices",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'targetVoices' должно быть массивом")
     *                   ),
     *                  @OA\Property(
     *                      property="targetVoices.*",
     *                      type="array",
     *                      @OA\Items(type="string", example="Выбранный целевой голос не существует.")
     *                  )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function addVoicesForCard(int $id, AddingVoiceForCardRequest $request)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if ($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if ($card->deck->user_id !== auth()->id()) {
            return ApiResponse::error("Текущий пользователь не является автором колоды, к которой принадлежит удаляемая карточка, поэтому её удаление невозможно.", null, 409);
        }
        $jobId = (string)Str::uuid();
        $this->jobStatusRepository->saveNewJobStatus($jobId, NameJobsEnum::GeneratingVoiceJob->value, JobStatuses::queued->value, auth()->id());
        GeneratingVoiceJob::dispatch($jobId, $request->originalVoices, $request->targetVoices, $id);
        return ApiResponse::success("Генерация произношения слов для карточки начата", (object)["job_id" => $jobId]);
    }

    /**
     * @OA\Post(
     *     path="/cards/{id}/singleAddingExample",
     *     summary="Добавление примера использования слова в карточку",
     *     description="Позволяет авторизованному пользователю добавить пример использования слова (оригинальный или перевод) в конкретную карточку.",
     *     operationId="addExampleToCard",
     *     tags={"Карточки"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID карточки, к которой добавляется пример",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingExampleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Пример успешно добавлен",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Пример использования слова на оригинальном языке успешно добавлен"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="info",
     *                     ref="#/components/schemas/InfoSavingExampleUsingWordInCardResource"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Карточка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Карточка с id = 123 не найдена"),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не автор карточки",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример"
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true, example = null)
     *         )
     *     ),
     *          @OA\Response(
     *          response=422,
     *          description="Ошибка валидации входных данных",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                       property="example",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'example' является обязательным")
     *                   ),
     *                  @OA\Property(
     *                      property="source",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле 'source' является обязательным")
     *                  ),
     *              )
     *          )
     *      ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function addExampleToCard(int $id, AddingExampleRequest $request)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if ($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if ($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример", null, 409);
        }
        $newExample = $this->exampleRepository->saveNewExample($request->example, $id, $request->source);
        $message = $request->source === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
        $resultInfo = ['text_example' => $request->example, "message" => $message];
        return ApiResponse::success($message, (object)['info' => new InfoSavingExampleUsingWordInCardResource($resultInfo)], 201);
    }


    /**
     * @OA\Post(
     *     path="/cards/{id}/multipleAddingExample",
     *     operationId="addMultipleExamplesToCard",
     *     tags={"Карточки"},
     *     summary="Добавление нескольких примеров к карточке",
     *     description="Позволяет автору колоды добавить несколько примеров использования слова в карточку",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID карточки, к которой добавляются примеры",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingMultipleExamplesRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Успешное добавление примеров",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Результат сохранения записей"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="info",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/InfoSavingExampleUsingWordInCardResource")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Карточка не найдена",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Карточка с id = 123 не найдена"),
     *             @OA\Property(property="errors", type="object", example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является автором колоды",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример"),
     *             @OA\Property(property="errors", type="object", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *           response=422,
     *           description="Ошибка валидации входных данных",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="The given data was invalid."),
     *               @OA\Property(
     *                   property="errors",
     *                   type="object",
     *                   @OA\Property(
     *                        property="examples",
     *                        type="array",
     *                        @OA\Items(type="string", example="Поле 'examples' должно быть массивом")
     *                    ),
     *                   @OA\Property(
     *                      property="examples.*.example",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле 'example' не должно превышать 255 символов.")
     *                   ),
     *                   @OA\Property(
     *                       property="examples.*.source",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'source' является обязательным.")
     *                   ),
     *               )
     *           )
     *       ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function addMultipleExamplesToCard(int $id, AddingMultipleExamplesRequest $request)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if ($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if ($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример", null, 409);
        }
        $messages = [];
        for ($number = 0; $number < count($request->examples); $number++) {
            $this->exampleRepository->saveNewExample($request->examples[$number]['example'], $id, $request->examples[$number]['source']);
            $message = $request->examples[$number]['source'] === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
            $infoMessage = ['number' => $number, 'text_example' => $request->examples[$number]['example'], "message" => $message];
            $messages[] = $infoMessage;
        }
        return ApiResponse::success("Результат сохранения записей", (object)['info' => InfoSavingExampleUsingWordInCardResource::collection($messages)], 201);
    }


}
