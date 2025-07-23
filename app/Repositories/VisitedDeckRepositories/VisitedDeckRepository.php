<?php

namespace App\Repositories\VisitedDeckRepositories;

use App\Models\VisitedDeck;

class VisitedDeckRepository implements VisitedDeckRepositoryInterface
{
    protected VisitedDeck $model;
    public function __construct(VisitedDeck $model)
    {
        $this->model = $model;
    }

    public function saveNewVisitedDeck($deckId, $userId): void
    {
        $newVisitedDeck = new VisitedDeck();
        $newVisitedDeck->deck_id = $deckId;
        $newVisitedDeck->user_id = $userId;
        $newVisitedDeck->save();
    }
}
