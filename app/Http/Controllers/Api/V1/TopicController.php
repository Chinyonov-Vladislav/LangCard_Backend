<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TopicRequests\CreatingTopicRequest;
use App\Http\Requests\Api\V1\TopicRequests\UpdatingTopicRequest;
use App\Http\Resources\V1\TopicResources\TopicResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\TopicRepositories\TopicRepositoryInterface;

class TopicController extends Controller
{
    protected TopicRepositoryInterface $topicRepository;

    public function __construct(TopicRepositoryInterface $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    /**
     * @OA\Get(
     *     path="/topics",
     *     operationId="getTopics",
     *     tags={"Топики"},
     *     summary="Получить список тем",
     *     description="Возвращает все темы из базы данных",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с коллекцией тем",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Темы из базы данных"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/TopicResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function getTopics()
    {
        $topics = $this->topicRepository->getAllTopics();
        return ApiResponse::success('Темы из базы данных', (object)['items' => new TopicResource($topics)]);
    }

    /**
     * @OA\Post(
     *     path="/topics",
     *     operationId="createTopic",
     *     tags={"Топики"},
     *     summary="Создание новой темы",
     *     description="Создает новую тему. Доступно только для администраторов.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatingTopicRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Тема успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Информация о новом топике успешно создана"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="item", ref="#/components/schemas/TopicResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array",
     *                     @OA\Items(type="string", example="Topic name is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin")
     * )
     */
    public function createTopic(CreatingTopicRequest $request)
    {
        $newTopic = $this->topicRepository->saveNewTopic($request->name);
        return ApiResponse::success('Информация о новом топике успешно создана', (object)['item' => new TopicResource($newTopic)], 201);
    }

    /**
     * @OA\Put(
     *     path="/topics/{id}",
     *     summary="Update an existing topic",
     *     description="Обновляет информацию о существующей теме по её ID. Доступно только для администраторов.",
     *     operationId="updateTopic",
     *     tags={"Топики"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID темы для обновления",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdatingTopicRequest")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Информация о топике успешно обновлена"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Топик с указанным ID не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Топик с id = 1 не найден"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Topic name is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin")
     * )
     */
    public function updateTopic(int $id, UpdatingTopicRequest $request)
    {
        $topic = $this->topicRepository->getTopicById($id);
        if ($topic === null) {
            return ApiResponse::error("Топик с id = $id не найден", null, 404);
        }
        $this->topicRepository->updateTopic($request->id, $request->name);
        return ApiResponse::success('Информация о топике успешно обновлена', null, 204);
    }

    /**
     * @OA\Delete(
     *     path="/topics/{id}",
     *     summary="Delete a topic",
     *     description="Удаляет тему по ID. Доступно только для администраторов.",
     *     operationId="deleteTopic",
     *     tags={"Топики"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID темы для удаления",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Топик был успешно удалён"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Топик не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Топик c id = 1 не найден"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *     @OA\Response(response=403, ref="#/components/responses/NotAdmin")
     * )
     */
    public function deleteTopic(int $id)
    {
        $topic = $this->topicRepository->getTopicById($id);
        if ($topic === null) {
            return ApiResponse::error("Топик c id = $id не найден", null, 404);
        }
        $this->topicRepository->deleteTopic($topic);
        return ApiResponse::success("Топик c id = $id был успешно удалён", null, 204);
    }
}
