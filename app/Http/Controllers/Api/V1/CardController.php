<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatuses;
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
                                JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->voiceRepository = $voiceRepository;
        $this->audiofileRepository = $audiofileRepository;
        $this->exampleRepository = $exampleRepository;
        $this->userTestResultRepository = $userTestResultRepository;
        $this->jobStatusRepository = $jobStatusRepository;
    }

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
        $this->jobStatusRepository->saveNewJobStatus($jobId, "GeneratingVoiceJob", JobStatuses::queued->value, auth()->id());
        GeneratingVoiceJob::dispatch($jobId, $request->originalVoices, $request->targetVoices, $id);
        return ApiResponse::success("Генерация произношения слов для карточки начата", (object)["job_id" => $jobId]);
    }

    public function addExampleToCard(int $id, AddingExampleRequest $request)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример",null, 409);
        }
        $newExample = $this->exampleRepository->saveNewExample($request->example,$id, $request->source);
        $message = $request->source === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
        $resultInfo = ['text_example' => $request->example, "message" => $message];
        return ApiResponse::success($message, (object)['info'=>new InfoSavingExampleUsingWordInCardResource($resultInfo)],201);
    }

    public function addMultipleExamplesToCard(int $id, AddingMultipleExamplesRequest $request)
    {
        $card = $this->cardRepository->getCardById($id, ['deck']);
        if($card === null) {
            return ApiResponse::error("Карточка с id = $id не найдена", null, 404);
        }
        if($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример",null, 409);
        }
        $messages = [];
        for($number = 0; $number < count($request->examples); $number++) {
            $this->exampleRepository->saveNewExample($request->examples[$number]['example'], $id, $request->examples[$number]['source']);
            $message = $request->examples[$number]['source'] === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
            $infoMessage = ['number' => $number, 'text_example' => $request->examples[$number]['example'], "message" => $message];
            $messages[] = $infoMessage;
        }
        return ApiResponse::success("Результат сохранения записей", (object)['info'=>InfoSavingExampleUsingWordInCardResource::collection($messages)],201);
    }


}
