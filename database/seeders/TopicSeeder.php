<?php

namespace Database\Seeders;

use App\Helpers\Formatter;
use App\Repositories\TopicRepositories\TopicRepositoryInterface;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    protected TopicRepositoryInterface $topicRepository;
    protected Formatter $formatter;

    public function __construct(TopicRepositoryInterface $topicRepository)
    {
        $this->topicRepository = $topicRepository;
        $this->formatter = new Formatter();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [['name' => 'Математика'],
            ['name' => 'Физика'],
            ['name' => 'Химия'],
            ['name' => 'Информатика'],
            ['name' => 'Биология'],
            ['name' => 'История'],
            ['name' => 'Философия'],
            ['name' => 'Экономика'],
            ['name' => 'Психология'],
            ['name' => 'Литература']];
        foreach ($data as $item) {
            $normalizedNameTopic = $this->formatter->normalizeWord($item['name']);
            if($normalizedNameTopic === '')
            {
                continue;
            }
            if(!$this->topicRepository->isExistByName($normalizedNameTopic))
            {
                $this->topicRepository->saveNewTopic($normalizedNameTopic);
            }
        }
    }
}
