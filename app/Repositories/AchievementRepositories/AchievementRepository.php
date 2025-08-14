<?php

namespace App\Repositories\AchievementRepositories;

use App\Models\Achievement;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

class AchievementRepository implements AchievementRepositoryInterface
{
    protected Achievement $model;

    public function __construct(Achievement $model)
    {
        $this->model = $model;
    }

    public function saveNewAchievement(string $title, ?string $description, ?string $icon, int $target): Achievement
    {
        $newAchievement = new Achievement();
        $newAchievement->title = $title;
        $newAchievement->description = $description;
        $newAchievement->icon = $icon;
        $newAchievement->target = $target;
        $newAchievement->save();
        return $newAchievement;
    }

    public function getAllAchievements(): Collection
    {
        return $this->model->all();
    }

    public function getAchievementsForUserWithProgressAndPagination(int $userId, PaginatorService $paginatorService, int $countOnPage, int $numberPage): array
    {
        $query = $this->model->with(['users' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }]);
        $data = $paginatorService->paginate($query, $countOnPage, $numberPage);
        $metadataPagination = $paginatorService->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function getAchievementById(int $id): ?Achievement
    {
        return $this->model->where('id', '=', $id)->first();
    }
}
