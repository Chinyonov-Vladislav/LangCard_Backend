<?php

namespace Database\Seeders;

use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\TestRepositories\TestRepositoryInterface;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    protected DeckRepositoryInterface $deckRepository;
    protected TestRepositoryInterface $testRepository;

    public function __construct(DeckRepositoryInterface $deckRepository, TestRepositoryInterface $testRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->testRepository = $testRepository;
    }

    // TODO добавить проверку, что пользователь, который добавляет тест, также является автором колоды, к которой добавляется тест
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name'=>'Контрольный тест: животные',
                'time_seconds'=>1200,
                'count_attempts'=>3,
                'deck_id'=>1
            ],
        ];
        foreach ($data as $item) {
            if(!$this->deckRepository->isExistDeckById($item['deck_id'])) {
                continue;
            }
            $this->testRepository->saveNewTest($item['name'], $item['time_seconds'], $item['count_attempts'], $item['deck_id']);
        }
    }
}
