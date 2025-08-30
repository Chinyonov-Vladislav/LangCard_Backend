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

    public function saveNewExample(string $textExample, int $cardId, string $source): Example
    {
        $newExample = new Example();
        $newExample->name = $textExample;
        $newExample->card_id = $cardId;
        $newExample->source = $source;
        $newExample->save();
        return $newExample;
    }

    public function getExampleById(int $id): ?Example
    {
        return $this->model->with(['card'=>function ($query) {
            $query->with(['deck']);
        }])->where('id', '=', $id)->first();
    }

    public function deleteExampleById(int $id): void
    {
        $this->model->where('id', '=', $id)->delete();
    }

    public function updateExample(string $textExample, string $source, int $exampleId): void
    {
        $this->model->where('id', '=', $exampleId)->update(['name' => $textExample, 'source' => $source]);
    }
}
