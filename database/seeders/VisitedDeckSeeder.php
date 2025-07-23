<?php

namespace Database\Seeders;

use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Repositories\VisitedDeckRepositories\VisitedDeckRepositoryInterface;
use Illuminate\Database\Seeder;

class VisitedDeckSeeder extends Seeder
{
    protected UserRepositoryInterface $userRepository;
    protected DeckRepositoryInterface $deckRepository;
    protected VisitedDeckRepositoryInterface $visitedDeckRepository;
    public function __construct(UserRepositoryInterface $userRepository,
                                DeckRepositoryInterface $deckRepository,
                                VisitedDeckRepositoryInterface $visitedDeckRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->userRepository = $userRepository;
        $this->visitedDeckRepository = $visitedDeckRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['deck_id'=>1, 'user_id'=>1],
        ];
        foreach ($data as $item) {
            if(!$this->deckRepository->isExistDeckById($item['deck_id']) || !$this->userRepository->isExistUserById($item['user_id'])) {
                continue;
            }
            $this->visitedDeckRepository->saveNewVisitedDeck($item['deck_id'], $item['user_id']);
        }
    }

}
