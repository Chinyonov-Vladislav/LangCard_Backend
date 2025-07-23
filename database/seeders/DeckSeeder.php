<?php

namespace Database\Seeders;

use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Illuminate\Database\Seeder;

class DeckSeeder extends Seeder
{
    protected LanguageRepositoryInterface $languageRepository;
    protected UserRepositoryInterface $userRepository;

    protected DeckRepositoryInterface $deckRepository;
    public function __construct(LanguageRepositoryInterface $languageRepository,
                                UserRepositoryInterface $userRepository, DeckRepositoryInterface $deckRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->userRepository = $userRepository;
        $this->deckRepository = $deckRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name'=>'Животные', 'original_language_id'=>5,'target_language_id'=>1, 'user_id'=>1, 'is_premium'=>false],
        ];
        foreach ($data as $item) {
            if(!($this->languageRepository->isExistLanguageById($item['original_language_id']) &&
                $this->languageRepository->isExistLanguageById($item['target_language_id'])) || !$this->userRepository->isExistUserById($item['user_id'])){
                continue;
            }
            $this->deckRepository->saveNewDeck($item['name'], $item['original_language_id'], $item['target_language_id'], $item['user_id'], $item['is_premium']);
        }

    }
}
