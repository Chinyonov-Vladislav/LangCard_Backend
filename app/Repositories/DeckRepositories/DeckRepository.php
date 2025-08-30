<?php

namespace App\Repositories\DeckRepositories;

use App\Enums\TypeInfoAboutDeck;
use App\Http\Filters\FiltersForModels\DeckFilter;
use App\Models\Deck;
use App\Services\PaginatorService;

class DeckRepository implements DeckRepositoryInterface
{
    protected Deck $model;

    public function __construct(Deck $model)
    {
        $this->model = $model;
    }

    public function saveNewDeck(string $name, int $originalLanguageId, int $targetLanguageId, int $userId, bool $isPremium): Deck
    {
        $newDeck = new Deck();
        $newDeck->name = $name;
        $newDeck->original_language_id = $originalLanguageId;
        $newDeck->target_language_id = $targetLanguageId;
        $newDeck->user_id = $userId;
        $newDeck->is_premium = $isPremium;
        $newDeck->save();
        return $newDeck;
    }

    public function isExistDeckById(int $id): bool
    {
        return $this->model->where('id', '=', $id)->exists();
    }

    public function getDecksWithPaginationAndFilters(PaginatorService $paginator, DeckFilter $deckFilter, int $countOnPage, int $numberCurrentPage): array
    {
        $query = $this->model->with(['originalLanguage', 'targetLanguage', 'user', 'topics'])
            ->withCount(['visitors', 'tests', 'cards'])
            ->filter($deckFilter);
        $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function getDeckById(int $id, TypeInfoAboutDeck $typeInfoAboutDeck): ?Deck
    {
        $query = $this->model->where('id', '=', $id);
        if ($typeInfoAboutDeck === TypeInfoAboutDeck::maximum) {
            $query->with(['originalLanguage',
                'targetLanguage',
                'user',
                'topics',
                'cards'=>function ($query) {
                    $query->with(['audiofilesForOriginalWord', 'audiofilesForTargetWord']);
                }]);
            $query->withCount(['visitors', 'tests', 'cards']);
            if (auth()->check()) {
                $query->with(['tests' => function ($query) {
                    $userId = auth()->id();
                    if ($userId !== null) {
                        $query->withCount([
                            'userTestResults as authorized_user_attempts' => function ($q) use ($userId) {
                                $q->where('user_id', $userId);
                            }
                        ]);
                    }
                }]);
            }
        }
        return $query->first();
    }

    public function deleteDeckById(int $id): void
    {
        $this->model->where('id', '=', $id)->forceDelete();
    }

    public function deleteDeckByDeckObject(Deck $deck): void
    {
        $deck->forceDelete();
    }

    public function softDeleteDeckById(int $id): void
    {
        $this->model->where('id', '=', $id)->delete();
    }

    public function softDeleteDeckByDeckObject(Deck $deck): void
    {
        $deck->delete();
    }
}
