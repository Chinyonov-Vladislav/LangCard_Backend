<?php

namespace Database\Seeders;

use App\Helpers\Formatter;
use App\Helpers\ValidationExampleUsageTranslatedWord;
use App\Repositories\CardRepositories\CardRepository;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\ExampleRepositories\ExampleRepositoryInterface;
use Illuminate\Database\Seeder;

class ExampleSeeder extends Seeder
{
    protected CardRepositoryInterface $cardRepository;
    protected ExampleRepositoryInterface $exampleRepository;

    protected Formatter $formatter;
    protected ValidationExampleUsageTranslatedWord  $validationExampleUsageTranslatedWord;
    public function __construct(CardRepositoryInterface $cardRepository, ExampleRepositoryInterface $exampleRepository)
    {
        $this->cardRepository = $cardRepository;
        $this->exampleRepository = $exampleRepository;
        $this->formatter = new Formatter();
        $this->validationExampleUsageTranslatedWord = new ValidationExampleUsageTranslatedWord();
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'The dog is barking loudly',
                'card_id' => 1,
            ],
            [
                'name' => 'I walk my dog every morning',
                'card_id' => 1,
            ],
            [
                'name' => 'The cat is sleeping on the sofa.',
                'card_id' => 2,
            ],
            [
                'name' => 'My cat loves chasing birds',
                'card_id' => 2,
            ],
            [
                'name' => 'The horse is running in the field',
                'card_id' => 3,
            ],
            [
                'name' => 'He rides his horse every weekend',
                'card_id' => 3,
            ]
        ];
        foreach ($data as $item) {
            if(!$this->cardRepository->isExistCardById($item['card_id'])) {
                continue;
            }
            $translatedWord = $this->cardRepository->getCardById($item['card_id'])->translate;
            $normalizedExample = $this->formatter->normalizeSentence($item['name']);
            if(!$this->validationExampleUsageTranslatedWord->validateTranslatedWord($normalizedExample, $translatedWord)) {
                continue;
            }
            $this->exampleRepository->saveNewExample($normalizedExample, $item['card_id']);
        }
    }
}
