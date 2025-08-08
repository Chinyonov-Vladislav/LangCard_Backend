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
    public function getTopics()
    {
        $topics = $this->topicRepository->getAllTopics();
        return ApiResponse::success('Темы из базы данных', (object)['items'=>new TopicResource($topics)]);
    }

    public function createTopic(CreatingTopicRequest $request)
    {
        $newTopic = $this->topicRepository->saveNewTopic($request->name);
        return ApiResponse::success('Информация о новом топике успешно создана', (object)['item'=>new TopicResource($newTopic)], 201);
    }
    public function updateTopic(UpdatingTopicRequest $request)
    {
        $this->topicRepository->updateTopic($request->id, $request->name);
        return ApiResponse::success('Информация о топике успешно обновлена', null, 204);
    }

    public function deleteTopic(int $id)
    {
        $topic = $this->topicRepository->getTopicById($id);
        if($topic === null)
        {
            return ApiResponse::error("Топик c id = $id не найден", null, 404);
        }
        $this->topicRepository->deleteTopic($topic);
        return ApiResponse::success("Топик c id = $id был успешно удалён", null, 204);
    }
}
