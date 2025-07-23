<?php

namespace App\Repositories\VisitedDeckRepositories;

interface VisitedDeckRepositoryInterface
{
    public function saveNewVisitedDeck($deckId, $userId);
}
