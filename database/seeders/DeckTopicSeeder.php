<?php

namespace Database\Seeders;

use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\DeckTopicRepositories\DeckTopicRepositoryInterface;
use App\Repositories\TopicRepositories\TopicRepositoryInterface;
use Illuminate\Database\Seeder;

class DeckTopicSeeder extends Seeder
{
    protected DeckRepositoryInterface $deckRepository;
    protected TopicRepositoryInterface $topicRepository;

    protected DeckTopicRepositoryInterface $deckTopicRepository;

    public function __construct(DeckRepositoryInterface $deckRepository,
                                TopicRepositoryInterface $topicRepository,
                                DeckTopicRepositoryInterface $deckTopicRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->topicRepository = $topicRepository;
        $this->deckTopicRepository = $deckTopicRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['deck_id'=>1, 'topic_id'=>5],
        ];
        foreach ($data as $item) {
            if(!$this->deckRepository->isExistDeckById($item['deck_id']) || !$this->topicRepository->isExistById($item['topic_id'])) {
                continue;
            }
            $this->deckTopicRepository->saveNewDeckTopic($item['deck_id'], $item['topic_id']);
        }
    }
}
