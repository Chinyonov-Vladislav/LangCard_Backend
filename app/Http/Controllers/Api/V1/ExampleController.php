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


    /**
     * @OA\Put(
     *     path="/examples/singleUpdate",
     *     operationId="updateSingleExample",
     *     tags={"Пример употребления слова"},
     *     summary="Обновление одного примера использования слова в карточке",
     *     description="Позволяет авторизованному пользователю обновить один пример использования слова в карточке, если он является автором колоды.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSingleExampleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пример успешно обновлен",
     *
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Пример употребления с id = 123 был успешно обновлен"),
     *             @OA\Property(property="data", type="object",nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является автором колоды",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может удалить пример"),
     *             @OA\Property(property="errors", type="object",nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации запроса",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="example_id",
     *                      type="array",
     *                      @OA\Items(type="string", example="Поле 'example_id' обязательно для заполнения")
     *                  ),
     *                 @OA\Property(
     *                       property="example",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'example' обязательно для заполнения")
     *                   ),
     *                @OA\Property(
     *                       property="source",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'source' обязательно для заполнения")
     *                   ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/examples/multipleUpdate",
     *     summary="Массовое обновление примеров употребления",
     *     description="Обновляет несколько примеров употребления слов в карточках. Возвращает результат по каждому примеру.",
     *     operationId="updateMultipleExample",
     *     tags={"Пример употребления слова"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateMultipleExamplesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Результат массового обновления примеров",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Результат обновления примеров употребления"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="result_info",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ResultUpdateMultipleExamplesResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации запроса",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
     *             @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                       property="examples",
     *                       type="array",
     *                       @OA\Items(type="string", example="Поле 'examples' должно быть массивом")
     *                   ),
     *                  @OA\Property(
     *                        property="examples.*.id",
     *                        type="array",
     *                        @OA\Items(type="string", example="Поле 'id' обязательно для заполнения")
     *                    ),
     *                 @OA\Property(
     *                        property="examples.*.example",
     *                        type="array",
     *                        @OA\Items(type="string", example="Поле 'example' обязательно для заполнения")
     *                    ),
     *                 @OA\Property(
     *                         property="examples.*.source",
     *                         type="array",
     *                         @OA\Items(type="string", example="Поле 'source' обязательно для заполнения")
     *                     ),
     *              )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/examples/{id}",
     *     summary="Удаление примера употребления",
     *     description="Удаляет пример употребления по его ID. Только автор колоды может удалить пример.",
     *     operationId="deleteExample",
     *     tags={"Пример употребления слова"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID примера, который нужно удалить",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пример успешно удален",
     *         @OA\JsonContent(
     *             type = "object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Запись примера употребления с id = 123 была успешно удалена"),
     *             @OA\Property(property="data", type="object",nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пример не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Пример употребления с id = 123 не найден"),
     *             @OA\Property(property="errors", type="object", nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является автором колоды",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Авторизованный пользователь не является автором колоды, которой принадлежит карточка, поэтому он не может удалить пример"),
     *             @OA\Property(property="errors", type="object", nullable = true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
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
