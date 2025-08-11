<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExampleRequests\AddingExampleRequest;
use App\Http\Requests\Api\V1\ExampleRequests\AddingMultipleExamplesRequest;
use App\Http\Requests\Api\V1\ExampleRequests\UpdateMultipleExamplesRequest;
use App\Http\Requests\Api\V1\ExampleRequests\UpdateSingleExampleRequest;
use App\Http\Resources\V1\ExampleResources\InfoSavingExampleUsingWordInCardResource;
use App\Http\Resources\V1\ExampleResources\ResultUpdateMultipleExamplesResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\ExampleRepositories\ExampleRepositoryInterface;

class ExampleController extends Controller
{
    protected ExampleRepositoryInterface $exampleRepository;

    protected CardRepositoryInterface $cardRepository;

    public function __construct(ExampleRepositoryInterface $exampleRepository, CardRepositoryInterface $cardRepository)
    {
        $this->exampleRepository = $exampleRepository;
        $this->cardRepository = $cardRepository;
    }

    public function addExampleToCard(AddingExampleRequest $request)
    {
        $card = $this->cardRepository->getCardById($request->card_id, ['deck']);
        if($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример",null, 409);
        }
        $newExample = $this->exampleRepository->saveNewExample($request->example, $request->card_id, $request->source);
        $message = $request->source === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
        $resultInfo = ['text_example' => $request->example, "message" => $message];
        return ApiResponse::success($message, (object)['info'=>new InfoSavingExampleUsingWordInCardResource($resultInfo)],201);
    }

    public function addMultipleExamplesToCard(AddingMultipleExamplesRequest $request)
    {
        $card = $this->cardRepository->getCardById($request->card_id, ['deck']);
        if($card->deck->user_id !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может добавить пример",null, 409);
        }
        $messages = [];
        for($number = 0; $number < count($request->examples); $number++) {
            $this->exampleRepository->saveNewExample($request->examples[$number]['example'], $request->card_id, $request->examples[$number]['source']);
            $message = $request->examples[$number]['source'] === "original" ? "Пример использования слова на оригинальном языке успешно добавлен" : "Пример использования слова на целевом языке успешно добавлен";
            $infoMessage = ['number' => $number, 'text_example' => $request->examples[$number]['example'], "message" => $message];
            $messages[] = $infoMessage;
        }
        return ApiResponse::success("Результат сохранения записей", (object)['info'=>InfoSavingExampleUsingWordInCardResource::collection($messages)],201);
    }

    public function updateSingleExample(UpdateSingleExampleRequest $request)
    {
        $exampleById = $this->exampleRepository->getExampleById($request->example_id);
        $ownerUserId = $exampleById->card->deck->user_id;
        if($ownerUserId !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может удалить пример",null, 409);
        }
        $this->exampleRepository->updateExample($request->example, $request->source, $request->example_id);
        return ApiResponse::success("Пример употребления с id = $request->example_id был успешно обновлен");
    }

    public function updateMultipleExample(UpdateMultipleExamplesRequest $request)
    {
        $data = [];
        for($number = 0; $number < count($request->examples); $number++)
        {
            $currentItemInfo = [];
            $currentItemInfo['number'] = $number;
            $currentItemInfo['text'] = $request->examples[$number]['example'];
            $exampleById = $this->exampleRepository->getExampleById($request->examples[$number]['id']);
            if($exampleById) {
                $currentItemInfo['success'] = TypeStatus::error->value;
                $currentItemInfo['message'] = "Пример употребления с id = {$request->examples[$number]['id']} не найден";
                $data[] = $currentItemInfo;
                continue;
            }
            $ownerUserId = $exampleById->card->deck->user_id;
            if($ownerUserId !== auth()->user()->id) {
                $currentItemInfo['success'] = TypeStatus::error->value;
                $currentItemInfo['message'] ="Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может удалить пример";
                $data[] = $currentItemInfo;
                continue;
            }
            $this->exampleRepository->updateExample($request->examples[$number]['example'], $request->examples[$number]['source'], $request->examples[$number]['id']);
            $currentItemInfo['success'] = TypeStatus::success->value;
            $currentItemInfo['message'] = "Пример употребления был успешно отредактирован";
            $data[] = $currentItemInfo;
        }
        return ApiResponse::success('Результат обновления примеров употребления', (object)['result_info'=>ResultUpdateMultipleExamplesResource::collection($data)]);
    }

    public function deleteExample(int $id)
    {
        $exampleById = $this->exampleRepository->getExampleById($id);
        if($exampleById) {
            return ApiResponse::error("Пример употребления с id = $id не найден",null, 404);
        }
        $ownerUserId = $exampleById->card->deck->user_id;
        if($ownerUserId !== auth()->user()->id) {
            return ApiResponse::error("Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может удалить пример",null, 409);
        }
        $this->exampleRepository->deleteExampleById($id);
        return ApiResponse::success("Запись примера употребления с id = $id была успешно удалена");
     }
}
