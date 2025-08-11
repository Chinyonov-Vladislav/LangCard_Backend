<?php

namespace App\Repositories\ExampleRepositories;

use App\Models\Example;

interface ExampleRepositoryInterface
{
    public function getExampleById(int $id) : ?Example;
    public function saveNewExample(string $textExample, int $cardId, string $source);

    public function updateExample(string $textExample, string $source, int $exampleId);
    public function deleteExampleById(int $id);
}
