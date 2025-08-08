<?php

namespace App\Repositories\TopicRepositories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Collection;

class TopicRepository implements TopicRepositoryInterface
{
    protected Topic $model;

    public function __construct(Topic $model)
    {
        $this->model = $model;
    }

    public function saveNewTopic(string $topicName)
    {
        $newTopic = new Topic();
        $newTopic->name = $topicName;
        $newTopic->save();
        return $newTopic;
    }

    public function isExistByName(string $name): bool
    {
        return $this->model->where('name', '=', $name)->exists();
    }

    public function isExistById(int $id): bool
    {
        return $this->model->where('id', '=', $id)->exists();
    }

    public function getAllTopics(): Collection
    {
        return $this->model->select(['id', 'name'])->get();
    }

    public function updateTopic(int $id, string $nameTopic)
    {
        $topic = $this->model->find($id);
        $topic->name = $nameTopic;
        $topic->save();
        return $topic;
    }

    public function getTopicById(int $id): ?Topic
    {
        return $this->model->where('id','=', $id)->select(['id', 'name'])->first();
    }

    public function deleteTopicById(int $id): void
    {
        $this->model->where('id','=', $id)->delete();
    }

    public function deleteTopic(Topic $topic): void
    {
        $topic->delete();
    }
}
