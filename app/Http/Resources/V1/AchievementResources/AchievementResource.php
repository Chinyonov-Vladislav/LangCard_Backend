<?php

namespace App\Http\Resources\V1\AchievementResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="AchievementResource",
 *     title="Achievement Resource (Ресурс достижения)",
 *     description="Информация о достижении, включая прогресс и дату разблокировки для авторизованного пользователя",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Внутренний идентификатор достижения",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Название достижения",
 *         example="Мастер карточек"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Подробное описание достижения",
 *         example="Разблокируется после создания 100 карточек"
 *     ),
 *     @OA\Property(
 *         property="icon",
 *         type="string",
 *         nullable=true,
 *         description="URL или путь до иконки достижения",
 *         example="https://example.com/icons/master.png"
 *     ),
 *     @OA\Property(
 *         property="target",
 *         type="integer",
 *         description="Целевое значение для получения достижения",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="progress",
 *         type="integer",
 *         description="Текущее значение прогресса пользователя по достижению",
 *         example=45
 *     ),
 *     @OA\Property(
 *         property="unlocked_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Дата и время, когда достижение было разблокировано пользователем (null, если ещё не достигнуто)",
 *         example="2025-08-14T12:34:56Z"
 *     )
 * )
 */
class AchievementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "title"=>$this->title,
            "description"=>$this->description,
            "icon"=>$this->icon,
            "target"=>$this->target,
            "progress"=>$this->users[0]->pivot->progress,
            "unlocked_at"=>$this->users[0]->pivot->unlocked_at
                ? $this->users[0]->pivot->unlocked_at->format('Y-m-d H:i:s')
                : null ,
        ];
    }
}
