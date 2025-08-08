<?php

namespace App\Repositories\TopicRepositories;

use App\Models\Topic;

interface TopicRepositoryInterface
{
    public function isExistById(int $id): bool;
    public function isExistByName(string $name): bool;
    public function getAllTopics();

    public function getTopicById(int $id): ?Topic;

    public function saveNewTopic(string $topicName);
    public function updateTopic(int $id, string $nameTopic);
    public function deleteTopicById(int $id);

    public function deleteTopic(Topic $topic);


}
