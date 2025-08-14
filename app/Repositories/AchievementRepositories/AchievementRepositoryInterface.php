<?php

namespace App\Repositories\AchievementRepositories;

use App\Models\Achievement;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

interface AchievementRepositoryInterface
{
    public function getAllAchievements(): Collection;

    public function getAchievementById(int $id): ?Achievement;

    public function getAchievementsForUserWithProgressAndPagination(int $userId, PaginatorService $paginatorService, int $countOnPage, int $numberPage): array;

    public function saveNewAchievement(string $title, ?string $description, ?string $icon, int $target): Achievement;
}
