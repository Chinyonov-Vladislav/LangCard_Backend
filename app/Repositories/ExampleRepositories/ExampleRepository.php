<?php

namespace App\Repositories\ExampleRepositories;

use App\Models\Example;

class ExampleRepository implements ExampleRepositoryInterface
{
    protected Example $model;

    public function __construct(Example $model)
    {
        $this->model = $model;
    }

    public function saveNewExample(string $textExample, int $cardId)
    {
        $newExample = new Example();
        $newExample->name = $textExample;
        $newExample->card_id = $cardId;
        $newExample->save();
    }
}
