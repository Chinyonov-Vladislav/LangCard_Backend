<?php

namespace App\Repositories\TopicRepositories;

use App\Models\Topic;

class TopicRepository implements TopicRepositoryInterface
{
    protected Topic $model;

    public function __construct(Topic $model)
    {
        $this->model = $model;
    }

    public function saveNewTopic($topicName)
    {
        $newTopic = new Topic();
        $newTopic->name = $topicName;
        $newTopic->save();
    }

    public function isExistByName(string $name): bool
    {
        return $this->model->where('name', '=', $name)->exists();
    }

    public function isExistById(int $id): bool
    {
        return $this->model->where('id', '=', $id)->exists();
    }
}
