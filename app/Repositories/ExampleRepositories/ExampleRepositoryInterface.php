<?php

namespace App\Repositories\ExampleRepositories;

interface ExampleRepositoryInterface
{
    public function saveNewExample(string $textExample, int $cardId);
}
