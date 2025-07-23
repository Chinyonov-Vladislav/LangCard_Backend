<?php

namespace App\Repositories\CardRepositories;

use App\Models\Card;

interface CardRepositoryInterface
{
    public function isExistCardById(int $idCard): bool;
    public function isExistCardByDeckIdAndWord($deckId, $word);

    public function getCardById(int $idCard): ?Card;
    public function saveNewCard(string $word, string $translate, ?string $image_url, $deckId): Card;

    public function addImageToCard(int $idCard, string $imageUrl);

    public function addPronunciationToCard(int $idCard, string $pronunciationUrl);
}
