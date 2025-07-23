<?php

namespace App\Repositories\DeckTopicRepositories;

use App\Models\DeckTopic;

class DeckTopicRepository implements DeckTopicRepositoryInterface
{
    protected DeckTopic $model;

    public function __construct(DeckTopic $model)
    {
        $this->model = $model;
    }

    public function saveNewDeckTopic(int $deckId, int $topicId)
    {
        $newDeckTopic = new DeckTopic();
        $newDeckTopic->topic_id = $topicId;
        $newDeckTopic->deck_id = $deckId;
        $newDeckTopic->save();
    }
}
