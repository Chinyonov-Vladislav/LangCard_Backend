<?php

namespace App\Repositories\VoiceRepositories;

use App\Http\Filters\FiltersForModels\VoiceFilter;
use App\Models\Voice;
use App\Services\PaginatorService;

interface VoiceRepositoryInterface
{
    public function getUnusedVoicesForCardFromArrayVoiceId(array $voiceIds, int $cardId, string $destination);

    public function getVoicesByVoiceId(array $voiceIds);

    public function isExistVoice(string $voiceId): bool;

    public function updateStatusActive(string $voiceId, bool $status);

    public function getVoicesWithPaginationAndFilters(PaginatorService $paginator, VoiceFilter $voiceFilter, int $countOnPage, int $numberCurrentPage);
    public function getVoiceByVoiceId(int $voiceId): ?Voice;
    public function saveNewVoice(string $voiceId, string $voiceName, string $sex,bool $status, int $languageId): Voice;
}
