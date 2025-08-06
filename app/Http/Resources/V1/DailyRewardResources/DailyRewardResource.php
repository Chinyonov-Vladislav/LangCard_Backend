<?php

namespace App\Http\Resources\V1\DailyRewardResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DailyRewardResource",
 *     title="DailyRewardResource (Ресурс ежедневной награды)",
 *     description="Информация об одной ежедневной награде пользователя",
 *     type="object",
 *     required={"id", "number_date", "reward", "is_take"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="ID записи награды"
 *     ),
 *     @OA\Property(
 *         property="number_date",
 *         type="integer",
 *         example=3,
 *         description="Номер дня (например, 3 — третий день подряд)"
 *     ),
 *     @OA\Property(
 *         property="reward",
 *         type="integer",
 *         example=100,
 *         description="Размер награды (например, количество монет)"
 *     ),
 *     @OA\Property(
 *         property="is_take",
 *         type="boolean",
 *         example=true,
 *         description="Флаг, получена ли награда пользователем"
 *     )
 * )
 */
class DailyRewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number_date' => $this->number_date,
            'reward' => $this->reward,
            'is_take' => (bool)$this->is_take,
        ];
    }
}
