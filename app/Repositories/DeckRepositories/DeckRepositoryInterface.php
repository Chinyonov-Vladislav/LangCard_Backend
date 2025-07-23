<?php

namespace App\Repositories\DeckRepositories;

use App\Enums\TypeInfoAboutDeck;
use App\Http\Filters\FiltersForModels\DeckFilter;
use App\Models\Deck;
use App\Services\PaginatorService;

interface DeckRepositoryInterface
{
    public function getDeckById(int $id, TypeInfoAboutDeck $typeInfoAboutDeck): ?Deck;
    public function getDecksWithPaginationAndFilters(PaginatorService $paginator, DeckFilter $deckFilter, int $countOnPage, int $numberCurrentPage): array;

    public function isExistDeckById(int $id): bool;



    public function saveNewDeck(string $name, int $originalLanguageId, int $targetLanguageId, int $userId, bool $isPremium): Deck;

    public function deleteDeckById(int $id);

    public function deleteDeckByDeckObject(Deck $deck);

    public function softDeleteDeckById(int $id);

    public function softDeleteDeckByDeckObject(Deck $deck);
}
