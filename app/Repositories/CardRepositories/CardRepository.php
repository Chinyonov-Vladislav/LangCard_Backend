<?php

namespace App\Repositories\CardRepositories;

use App\Models\Card;

class CardRepository implements CardRepositoryInterface
{

    protected Card $model;

    public function __construct(Card $model)
    {
        $this->model = $model;
    }

    public function isExistCardByDeckIdAndWord($deckId, $word): bool
    {
        return $this->model->where('deck_id', '=', $deckId)->where('word', '=', $word)->exists();
    }

    public function saveNewCard(string $word, string $translate, ?string $image_url, $deckId): Card
    {
        $newCard = new Card();
        $newCard->word = $word;
        $newCard->translate = $translate;
        $newCard->image_url = $image_url;
        $newCard->deck_id = $deckId;
        $newCard->save();
        return $newCard;
    }

    public function isExistCardById(int $idCard): bool
    {
        return $this->model->where('id', '=', $idCard)->exists();
    }

    public function getCardById(int $idCard, array $withArray = []): ?Card
    {
        return $this->model->with($withArray)->where('id', '=', $idCard)->first();
    }

    public function addImageToCard(int $idCard, string $imageUrl): void
    {
        $this->model->where('id', '=', $idCard)->update(['image_url' => $imageUrl]);
    }

    public function addPronunciationToCard(int $idCard, string $pronunciationUrl)
    {
        $this->model->where('id', '=', $idCard)->update(['pronunciation_url' => $pronunciationUrl]);
    }
}
