<?php

namespace App\Repositories\DeckTopicRepositories;

interface DeckTopicRepositoryInterface
{
    public function saveNewDeckTopic(int $deckId, int $topicId);
}
