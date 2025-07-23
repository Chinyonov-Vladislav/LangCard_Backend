<?php

namespace Database\Seeders;

use App\Helpers\Formatter;
use App\Repositories\QuestionAnswerRepository\QuestionAnswerRepositoryInterface;
use App\Repositories\QuestionRepositories\QuestionRepositoryInterface;
use Illuminate\Database\Seeder;

class QuestionAnswerSeeder extends Seeder
{
    protected QuestionRepositoryInterface $questionRepository;
    protected QuestionAnswerRepositoryInterface $questionAnswerRepository;

    protected Formatter $formatter;

    public function __construct(QuestionRepositoryInterface $questionRepository, QuestionAnswerRepositoryInterface $questionAnswerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->questionAnswerRepository = $questionAnswerRepository;
        $this->formatter = new Formatter();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Вопрос 1: "Собака" — правильный ответ: Dog
            ['text_answer' => 'Dog', 'question_id' => 1, 'is_correct' => true],
            ['text_answer' => 'Cat', 'question_id' => 1, 'is_correct' => false],
            ['text_answer' => 'Cow', 'question_id' => 1, 'is_correct' => false],
            ['text_answer' => 'Horse', 'question_id' => 1, 'is_correct' => false],

            // Вопрос 2: "Insert missing word in: at night, the ___ silently climbed..." — Cat
            ['text_answer' => 'Cat', 'question_id' => 2, 'is_correct' => true],
            ['text_answer' => 'Dog', 'question_id' => 2, 'is_correct' => false],
            ['text_answer' => 'Mouse', 'question_id' => 2, 'is_correct' => false],
            ['text_answer' => 'Bird', 'question_id' => 2, 'is_correct' => false],

            // Вопрос 3: "Корова" — Cow
            ['text_answer' => 'Cow', 'question_id' => 3, 'is_correct' => true],
            ['text_answer' => 'Pig', 'question_id' => 3, 'is_correct' => false],
            ['text_answer' => 'Sheep', 'question_id' => 3, 'is_correct' => false],
            ['text_answer' => 'Goat', 'question_id' => 3, 'is_correct' => false],

            // Вопрос 4: "The ___ rolled in the mud..." — Pig
            ['text_answer' => 'Pig', 'question_id' => 4, 'is_correct' => true],
            ['text_answer' => 'Cow', 'question_id' => 4, 'is_correct' => false],
            ['text_answer' => 'Dog', 'question_id' => 4, 'is_correct' => false],
            ['text_answer' => 'Horse', 'question_id' => 4, 'is_correct' => false],
        ];
        foreach ($data as $item) {
            if(!$this->questionRepository->isExistQuestionById($item['question_id'])) {
                continue;
            }
            $normalizedAnswer = $this->formatter->normalizeWord($item['text_answer']);
            $cardForQuestion = $this->questionRepository->getQuestionWithCardById($item['question_id']);
            $correctAnswerText = $cardForQuestion->card->translate;
            // проверка: действительно ли ответ является корректным согласно карточке
            if($item['is_correct'] === true) {
                if($correctAnswerText !== $normalizedAnswer)
                {
                    continue;
                }
            }
            else
            {
                if($correctAnswerText === $normalizedAnswer)
                {
                    continue;
                }
            }
            if(!$this->questionAnswerRepository->isExistAnswerForQuestionByTextAnswer($normalizedAnswer, $item['question_id'])) {
                $this->questionAnswerRepository->saveNewAnswer($normalizedAnswer, $item['question_id'], $item['is_correct']);
            }
        }
    }
}
