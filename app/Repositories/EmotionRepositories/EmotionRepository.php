<?php

namespace App\Repositories\EmotionRepositories;

use App\Models\Emotion;
use Illuminate\Database\Eloquent\Collection;

class EmotionRepository implements EmotionRepositoryInterface
{
    protected Emotion $model;
    public function __construct(Emotion $model)
    {
        $this->model = $model;
    }

    public function saveNewEmotion(string $name, string $icon): Emotion
    {
        $newEmotion = new Emotion();
        $newEmotion->name = $name;
        $newEmotion->icon = $icon;
        $newEmotion->save();
        return $newEmotion;
    }

    public function getEmotions(): Collection
    {
        return $this->model->all();
    }

    public function getEmotion(int $id): ?Emotion
    {
        return $this->model->where("id", "=", $id)->first();
    }
}
