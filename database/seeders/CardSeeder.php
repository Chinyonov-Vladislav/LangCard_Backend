<?php

namespace Database\Seeders;

use App\Helpers\Formatter;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    protected DeckRepositoryInterface $deckRepository;
    protected CardRepositoryInterface $cardRepository;
    protected Formatter $formatter;

    public function __construct(DeckRepositoryInterface $deckRepository, CardRepositoryInterface $cardRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->formatter = new Formatter();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'word' => 'собака',
                'translate' => 'dog',
                'image_url' => null,
                'deck_id' => 1
            ],
            [
                'word' => 'кошка',
                'translate' => 'cat',
                'image_url' => null,
                'deck_id' => 1
            ],
            [
                'word' => 'лошадь',
                'translate' => 'horse',
                'image_url' => null,
                'deck_id' => 1
            ],
            [
                'word' => 'корова',
                'translate' => 'cow',
                'image_url' => null,
                'deck_id' => 1
            ],
            [
                'word' => 'свинья',
                'translate' => 'pig',
                'image_url' => null,
                'deck_id' => 1
            ],
        ];
        foreach ($data as $item)
        {
            if(!$this->deckRepository->isExistDeckById($item['deck_id']))
            {
                continue;
            }
            $normalizeWord = $this->formatter->normalizeWord($item['word']);
            $normalizeTranslate = $this->formatter->normalizeWord($item['translate']);
            if ($this->cardRepository->isExistCardByDeckIdAndWord($item['deck_id'], $normalizeWord))
            {
                continue;
            }
            $this->cardRepository->saveNewCard($normalizeWord, $normalizeTranslate, $item['image_url'], $item['deck_id']);
        }
    }
}
