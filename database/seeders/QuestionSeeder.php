<?php

namespace Database\Seeders;

use App\Helpers\Formatter;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\QuestionRepositories\QuestionRepositoryInterface;
use App\Repositories\TestRepositories\TestRepositoryInterface;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    protected TestRepositoryInterface $testRepository;
    protected CardRepositoryInterface $cardRepository;
    protected QuestionRepositoryInterface $questionRepository;
    protected Formatter $formatter;

    public function __construct(TestRepositoryInterface $testRepository, CardRepositoryInterface $cardRepository, QuestionRepositoryInterface $questionRepository)
    {
        $this->testRepository = $testRepository;
        $this->cardRepository = $cardRepository;
        $this->questionRepository = $questionRepository;
        $this->formatter = new Formatter();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'text' => 'Собака',
                'type' => 'translate',
                'card_id' =>1,
                'test_id' => 1,
            ],
            [
                'text' => 'at night, the cat silently climbed onto the roof, chasing the moonlight',
                'type' => 'insert',
                'card_id' =>2,
                'test_id' => 1,
            ],
            [
                'text' => 'Корова',
                'type' => 'translate',
                'card_id' =>4,
                'test_id' => 1,
            ],
            [
                'text' => 'The pig rolled in the mud to cool off on the hot summer day.',
                'type' => 'insert',
                'card_id' =>5,
                'test_id' => 1,
            ],
            [
                'text' => 'The pig rolled in the mud to cool off on the hot summer day.',
                'type' => 'insert',
                'card_id' =>7,
                'test_id' => 1,
            ],
        ];
        foreach ($data as $item) {
            $acceptedTypes = ['translate', 'insert'];
            if (!$this->testRepository->isExistTestById($item['test_id']) || !in_array($item['type'], $acceptedTypes)) {
                continue;
            }
            // добавить проверку, что пользователь, добавляющий вопрос к тесту, является автором теста

            //  проверка: карточка, используемая в вопросе теста, относится к той же колоде, к которой создан тест
            $isExistCardForQuestionInDeckOfTest = $this->questionRepository->isExistCardForQuestionInSameDeckAsTest($item['card_id'], $item['test_id']);
            if(!$isExistCardForQuestionInDeckOfTest)
            {
                continue;
            }
            $card = $this->cardRepository->getCardById($item['card_id']);
            if($item['type'] == 'insert') // добавление вопроса с типом "Вставка слова"
            {
                if($item['text'] === null)
                {
                    continue;
                }
                $translatedWord = $card->translate;
                $result = $this->formatter->replaceInsertedWord($this->formatter->normalizeSentence($item['text']), $translatedWord);
                if($result === false)
                {
                    continue;
                }
                $this->questionRepository->saveNewQuestion($result,$item['type'], $item['card_id'], $item['test_id']);
            }
            else // добавление вопроса с типом "Выбор перевода"
            {
                if($card->image_url === null && $item['text'] === null)
                {
                    continue;
                }
                $normalizedText = $item['text'];
                if(is_string($normalizedText))
                {
                    $normalizedText = $this->formatter->normalizeWord($normalizedText);
                }
                $this->questionRepository->saveNewQuestion($normalizedText,$item['type'], $item['card_id'], $item['test_id']);
            }
        }
    }
}
