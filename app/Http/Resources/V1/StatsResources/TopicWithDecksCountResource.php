<?php

namespace App\Http\Resources\V1\StatsResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TopicWithDecksCountResource",
 *     title="Topic with Decks Count and Percentage (ресурс статистики о количестве активных колод в каждой тематике с процентным соотношении)",
 *     description="Ресурс, содержащий информацию о теме, количестве колод и их процентном соотношении",
 *     type="object",
 *     required={"id", "name", "decks_count", "percentage"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор темы"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Mathematics",
 *         description="Название темы"
 *     ),
 *     @OA\Property(
 *         property="decks_count",
 *         type="integer",
 *         example=42,
 *         description="Количество колод в данной теме"
 *     ),
 *     @OA\Property(
 *         property="percentage",
 *         type="number",
 *         format="float",
 *         example=15.25,
 *         description="Процентное соотношение количества колод темы от общего количества колод"
 *     )
 * )
 */
class TopicWithDecksCountResource extends JsonResource
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
            'name' => $this->name,
            'decks_count' => $this->decks_count,
            'percentage' => $this->percentage,
        ];
    }
}
